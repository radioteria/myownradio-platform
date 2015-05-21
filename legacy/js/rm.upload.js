(function() {
    $("a.upload").live('click', function() {
        createForm("#uploaderTemplate", {}, 500, 220);
        return false;
    });
})();


(function(w) {
    var jobQueue = [];
    var procFlag = false;
    var currentFile = 0;
    var totalFiles = 0;
    var uploadHandle = false;

    var successfullyUploaded = 0;
    var totalFilesSize = 0;
    var summingFilesSize = 0;

    $(".rm_browse").live('click', function() { $(".rm_input_files").click(); });
    $(".rm_close").live('click', function()
    {
        uploadReset();
        formDestroy();
    });

    $(".rm_input_files").livequery(function()
    {
        $(this).on('change', function(event)
        {
            $.each(event.target.files, function(i, file)
            {
                if(file.size < 512 * 1024 * 1024)
                {
                    $("#total_id").text(++totalFiles);
                    jobQueue.push(file);
                    totalFilesSize += file.size;
                }
                else
                {
                    showInfo("One or more files has unsupported file size. Maximum size is 512 MB!");
                }
            });
            if (procFlag === false)
            {
                uploadNextFile();
            }
        });
    });

    function uploadReset()
    {
        jobQueue = [];
        if (uploadHandle !== false)
        {
            uploadHandle.abort();

        }
        procFlag = false;
        totalFiles = 0;
        currentFile = 0;
        successfullyUploaded = 0;
        totalFilesSize = 0;
        summingFilesSize = 0;
    }

    function uploadNextFile()
    {
        if (jobQueue.length === 0)
        {
            totalFiles = 0;
            currentFile = 0;
            procFlag = false;
            uploadReset();
            formDestroy();
            return;
        }

        currentFile++;
        procFlag = true;

        $(".rm_upload_progress_wrap").addClass("visible");
        $(".rm_upload_prompt").remove();

        var file = jobQueue.shift();

        $("#curr_name").html(file.name);
        $("#total_id").html(totalFiles);
        $("#curr_id").html(currentFile);

        var data = new FormData();
        data.append('file', file);
        data.append('authtoken', mor.user_token);

        uploadHandle = $.ajax({
            type: "POST",
            xhr: function() 
            {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) 
                {
                    myXhr.upload.addEventListener('progress', progressHandlingFunction, false);
                }
                return myXhr;
            },
            url: "/radiomanager/upload",
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            success: function(data) {
                uploadHandle = false;
                try
                {
                    var json = JSON.parse(data);
                    if (json.code === "UPLOAD_SUCCESS")
                    {
                        successfullyUploaded ++;
                        summingFilesSize += file.size;
                        callModuleFunction("tracklist.trackAdd", json.data.tid, json.data);
                        mor.tracks_count ++;
                        try { updateRadioManagerInterface() } catch(e) {}
                    }
                    else if (json.code === "UPLOAD_ERROR_NO_SPACE")
                    {
                        totalFiles = 0;
                        currentFile = 0;
                        procFlag = false;
                        uploadReset();
                        formDestroy();
                        myMessageBox("Not enought time left on your account!");
                    }
                    else
                    {
                        myMessageBox(file.name + ": " + json.code);
                    }
                }
                catch (e)
                {
      
                }
                uploadNextFile();
            },
            error: function() {
                uploadHandle = false;
            }
        });

    }

    function progressHandlingFunction(e) {
        if (e.lengthComputable && totalFilesSize > 0) {
            $("#progress_handle").width((100 / totalFilesSize * (summingFilesSize + e.loaded)) + "%");
        }
    }

})(window);