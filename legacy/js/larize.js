(function(){
    var settings = {
        'title' : "Larize Module Editor"
    };
    var openedModules = [];
    var activeModule = null
    var userToken = null;
    var editor = [];
    
    var oldX = 0;
    var resize = false;
    var savedWidth = 0;
    
    $(document).ready(function(){
        userToken = $("body").attr("token");
        $.ajaxSetup({
            headers: { 'My-Own-Token': userToken }
        });
        loadModules();
        resizeWindow();
        $("td#list").resizable({
            handles: 'e, w'
        });
    })
    .ajaxSend(function(){$("#ajax").addClass("visible");})
    .ajaxComplete(function(){$("#ajax").removeClass("visible");})
    .ajaxError(function(){$("#ajax").removeClass("visible");})
    .bind("mousedown", function(event){
        if($(event.target).is("div#lrz-resizer"))
        {
            oldX = event.pageX;
            savedWidth = $("td#list").width();
            resize = true;
            event.stopPropagation();
        }
    })
    .bind("mouseup", function(event){
        if(resize)
        {
            resize = false;
            event.stopPropagation();
        }
    })
    .bind("mousemove", function(event){
        if(resize) {
            event.stopPropagation();
            var delta = event.pageX - oldX;
            $("td#list").width(savedWidth + delta);
            return false;
        }
    });
    
    $(window).bind('resize', function(){
        resizeWindow();
    });
    
    function loadModules()
    {
        $.post("", { action : "list", authtoken : userToken }, function(p){
            $("ul#ml").empty();
            var pattern = $("#moduleList");
            if(p.error !== undefined)
            {
                alert(p.error);
                return;
            }
            var lastPrefix = "";
            for(var m in p)
            {
                var prefix = (p[m].name.indexOf('.') > -1) ? (p[m].name.substr(0, p[m].name.indexOf('.'))) : p[m].name;
                if(prefix !== lastPrefix)
                {
                    $("<li>").addClass("delimiter").text(prefix).appendTo("ul#ml");
                }
                if(p[m].alias.length > 0)
                {
                    p[m].active = "active";
                }
                pattern.tmpl(p[m]).appendTo("ul#ml");
                lastPrefix = prefix;
            }
        });        
    }
    
    function addNewModule(data)
    {
        $("#moduleList").tmpl(data).appendTo("ul#ml");
    }
    
    function openModule(name)
    {
        $.post("", { action : "get", module : name, authtoken : userToken }, function(p){
            try {
                if(p.error !== undefined)
                {
                    alert(p.error);
                    return;
                }
                openedModules[name] = {
                    html : { modified : false, data : p.html },
                    css  : { modified : false, data : p.css },
                    js   : { modified : false, data : p.js },
                    tmpl : { modified : false, data : p.tmpl },
                    post : { modified : false, data : p.post },
                    _loc : 'html'
                };
            }
            catch(e)
            {
                console.log(e);
            }
            openEditorTab(name, "html");
            loadEditorContents();
        });
    }
    
    function loadEditorContents()
    {
        var uid = "ea-" + activeModule.module + "-" + activeModule.section;
        if(editor[uid].getSession().getValue() !== openedModules[activeModule.module][activeModule.section].data)
        {
            editor[uid].getSession().setValue(openedModules[activeModule.module][activeModule.section].data);
            openedModules[activeModule.module][activeModule.section].modified = false;
            updateModifiers();
        }
    }
    
    function openEditorTab(module, section)
    {
        if(openedModules[module] === undefined)
        {
            console.log("No module");
            return;
        }
        
        activeModule = { module : module, section : section, modified : false };

        var uid = "ea-" + module + "-" + section;
        
        $(".switchable").hide();
        $(".editor").addClass("visible");
        document.title = module + " \u2014 " + settings.title;
        
        var selector = $(".switchable").filter(function(){ return $(this).attr("id") === uid; });

        console.log("Open Editor", editor[uid] !== undefined, selector.length);

        if(editor[uid] !== undefined)
        {
            selector.show();
        }
        else
        {
            selector.remove();
            $("<pre>")
                .addClass("switchable textarea")
                .attr({ 'id' : uid })
                .attr({ 'data-module' : module, 'data-section' : section })
                .text(openedModules[activeModule.module][activeModule.section].data)
                .appendTo($("div.switchers"));
        
            editor[uid] = ace.edit(uid);
            
            if(section === "html")
                editor[uid].session.setMode("ace/mode/php");
            else if(section === "js")
                editor[uid].session.setMode("ace/mode/javascript");
            else if(section === "css")
                editor[uid].session.setMode("ace/mode/css");
            else if(section === "tmpl")
                editor[uid].session.setMode("ace/mode/html");
            else if(section === "post")
                editor[uid].session.setMode("ace/mode/php");
            
            editor[uid].setTheme("ace/theme/chrome");
            editor[uid].setFontSize("10pt");
            editor[uid].setOptions({
                enableBasicAutocompletion: false,
                enableSnippets: true,
                enableLiveAutocompletion: false
            }); 
            
            editor[uid].getSession().on("change", function(element, target){
                    $("ul.sections > li[data-section='"+activeModule.section+"']").addClass("modified");
                    $("ul.mlist > li[data-module='"+activeModule.module+"']").addClass("modified");
                    openedModules[activeModule.module][activeModule.section].modified = true;
                    openedModules[activeModule.module][activeModule.section].data = editor[uid].getSession().getValue();
                    updateModifiers();
            });
            
            editor[uid].commands.addCommand({
                name: 'saveCommand',
                    bindKey: {
                        win: 'Ctrl-S',
                        mac: 'Command-S',
                        sender: 'editor'
                    }, exec: function(env, args, request) { 
                        if(openedModules[activeModule.module][activeModule.section].modified)
                        {
                            saveCurrentSection();
                        }
                    }
            });
            
            editor[uid].commands.addCommand({
                name: 'saveAllCommand',
                    bindKey: {
                        win: 'Ctrl-M',
                        mac: 'Command-M',
                        sender: 'editor'
                    }, exec: function(env, args, request) { 
                        if(openedModules[activeModule.module][activeModule.section].modified)
                        {
                            saveCurrentModule();
                        }
                    }
            });
            

        }
        
        openedModules[module]._loc = section;
        
        updateSelectors();
        updateModifiers();
        resizeWindow();
        
        editor[uid].focus();

    }
    
    function resizeWindow()
    {
        $(".switchers").height($(window).height() - $(".switchers").position().top - 8);
        $("ul#ml").height($(window).height() - $("ul#ml").position().top - $("#create").outerHeight() - 28);
    }
    
    function updateModifiers()
    {

        $(".textarea").removeClass("modified");
        $("ul.sections > li").removeClass("modified");
        $("ul.mlist > li").removeClass("modified");
        $(".saveModule.visible").removeClass("visible");
        $(".saveSection.visible").removeClass("visible");
        
        if(activeModule !== null && openedModules[activeModule.module] !== undefined)
        {
            if(openedModules[activeModule.module][activeModule.section].modified)
            {
                $(".saveSection").addClass("visible");
            }

            for(var sec in openedModules[activeModule.module])
            {
                if(openedModules[activeModule.module][sec].modified)
                {
                    $("ul.sections > li[data-section='"+sec+"']").addClass("modified");
                    $(".saveModule").addClass("visible");
                }
            }
        }
        
        for(var i in openedModules)
        {
            var data = openedModules[i];
            
            for(var sec in data)
            {
                if(data[sec].modified)
                {
                    $(".textarea[data-module='"+i+"']").addClass("modified");
                    $("ul.mlist > li[data-module='"+i+"']").addClass("modified");
                }
                
            }
        }
        
    }
    
    function updateSelectors()
    {
        $("ul.mlist").empty();
                
        for(var i in openedModules)
        {
            var data = openedModules[i];
            $("#openedList").tmpl({name:i}).appendTo($("ul.mlist"));
        }

        $("ul#ml > li.active").removeClass("active");
        $("ul.mlist > li.active").removeClass("active");
        $("ul.sections > li.active").removeClass("active");

        if(activeModule)
        {
            $("ul#ml > li[data-module='"+activeModule.module+"']").addClass("active");
            $("ul.mlist > li").filter("[data-module=\""+activeModule.module+"\"]").addClass("active");
            $("ul.sections > li").filter("."+activeModule.section).addClass("active");
        }
        
        documentHider();
        
    }
    
    function documentHider()
    {
        if($("ul.mlist > li.active").length > 0)
        {
            $(".editor").addClass("visible");
        }
        else
        {
            $(".editor").removeClass("visible");
            document.title = settings.title;
        }
    }
    
    function saveCurrentModule()
    {
        var post = {
            action    : "save",
            module    : activeModule.module, 
            authtoken : userToken
        };
        
        for (var i in openedModules[activeModule.module])
        {
            if(openedModules[activeModule.module][i].modified)
                post[i] = openedModules[activeModule.module][i].data;
        }
       
        $.post("", post, function(p){
            try {
                if(p.error !== undefined)
                {
                    alert(p.error);
                    return;
                }
                if(p.save !== undefined)
                {
                    openedModules[activeModule.module].html.modified = false;
                    openedModules[activeModule.module].js.modified = false;
                    openedModules[activeModule.module].css.modified = false;
                    openedModules[activeModule.module].tmpl.modified = false;
                    openedModules[activeModule.module].post.modified = false;
                    loadModules();
                    updateModifiers();
                }
            }
            catch(e)
            {
                
            }

        });
    }

    function saveCurrentSection()
    {
        var post = {
            action    : "save",
            module    : activeModule.module,
            authtoken : userToken
        };
        
        if(openedModules[activeModule.module][activeModule.section].modified)
        {
            post[activeModule.section] = openedModules[activeModule.module][activeModule.section].data;
        }
       
        $.post("", post, function(p){
            try {
                if(p.error !== undefined)
                {
                    alert(p.error);
                    return;
                }
                if(p.save !== undefined)
                {
                    openedModules[activeModule.module][activeModule.section].modified = false;
                    loadModules();
                    updateModifiers();
                }
            }
            catch(e)
            {
                
            }

            console.log(data);
        });
    }
    
    function closeModule(module)
    {
        var uid = "ea-" + module;
        for(var i in editor)
        {
            if(i.indexOf(uid) === 0)
            {
                console.log("Closed: " + i);
                delete(editor[i]);
                $(".switchable").filter(function(){return $(this).attr("id") === i;}).remove();
            }
        }
        delete(openedModules[module]);
        $("ul#ml > li[data-module='"+module+"'].active").removeClass("active");
        $("ul.mlist > li[data-module='"+module+"']").remove();
        if(activeModule !== null && module === activeModule.module)
        { 
            activeModule = null; 
        }
        updateSelectors()
        updateModifiers();
    }
    
    function createModule()
    {
        var name = prompt("Please enter the name for new module", "");
        if (name !== null) {
            if(openedModules[name] !== undefined)
            {
                alert("Module already exists!");
            }
            else
            {
                openedModules[name] = {
                    html : { data : "", modified : true },
                    css  : { data : "", modified : true },
                    js   : { data : "", modified : true },
                    tmpl : { data : "", modified : true },
                    post : { data : "", modified : true },
                    _loc : "html"
                };
                openEditorTab(name, "html");
            }
        }
    }
    
    /* Bingings */
    $("ul#ml > li:not(.delimiter)").live("click", function(){
        openModule($(this).attr("data-module"));
    });
    
    $("ul.sections > li").live("click", function(){
        var section = $(this).attr('data-section');
        openEditorTab(activeModule.module, section);
    });
    
    $("ul.mlist > li").live("click", function(){
        var module = $(this).attr('data-module');
        console.log("Open");
        if(openedModules[module] === undefined)
        {
            openEditorTab(module, "html");
        }
        else
        {
            openEditorTab(module, openedModules[module]._loc);
        }
    });
    
    $("#create").live("click", function(){
        createModule();
    });
    
    $(".saveModule").live("click", function(){
        saveCurrentModule();
    });

    $(".saveSection").live("click", function(){
        saveCurrentSection();
    });
    
    $(".closer").live("click", function(event){
        event.stopPropagation();
        var doc = $(this).attr("data-module");
        closeModule(doc);
    });
    
    $(".ml-alias").live("click", function(event){
        event.stopPropagation();
        var parent = $(this).parents(".listModulesElement");
        var name = prompt("Please enter the alias for this module", parent.attr("data-module-alias"));
        if (name !== null) {
            $.post("", {
                action : "alias",
                module : parent.attr("data-module"),
                alias  : name,
                authtoken : userToken
            }, function(data){
                console.log(data);
                loadModules();
                updateSelectors();
            });
        }
    });

})();
