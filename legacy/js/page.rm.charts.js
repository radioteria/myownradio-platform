$(document).ready(function(){
    var chartData = generateChartData();
    var chart1 = AmCharts.makeChart("chartdiv1", {
        "type": "serial",
        "theme": "light",
        "pathToImages": "http://www.amcharts.com/lib/3/images/",
        "dataProvider": chartData,
        "valueAxes": [{
            "axisAlpha": 0.2,
            "dashLength": 1,
            "position": "left"
        }],
        "graphs": [{
            "id":"g1",
            "balloonText": "[[value]]",
            "bullet": "round",
            "bulletBorderAlpha": 1,
         "bulletColor":"#FFFFFF",
            "hideBulletsCount": 50,
            "title": "red line",
            "valueField": "listeners",
      "useLineColorForBulletBorder":true
        }],
        "chartScrollbar": {
            "autoGridCount": true,
            "graph": "g1",
            "scrollbarHeight": 40
        },
        "chartCursor": {
            "cursorPosition": "mouse"
        },
        "categoryField": "date",
        "categoryAxis": {
            "parseDates": true,
            "axisColor": "#DADADA",
            "dashLength": 1,
            "minorGridEnabled": true
        },
     "exportConfig":{
       menuRight: '20px',
          menuBottom: '30px',
          menuItems: [{
          icon: 'http://www.amcharts.com/lib/3/images/export.png',
          format: 'png'   
          }]  
     }
    });
    
    var chart2 = AmCharts.makeChart("chartdiv2", {
        "type": "serial",
        "theme": "light",
        "pathToImages": "http://www.amcharts.com/lib/3/images/",
        "dataProvider": chartData,
        "valueAxes": [{
            "axisAlpha": 0.2,
            "dashLength": 1,
            "position": "left"
        }],
        "graphs": [{
            "id":"g1",
            "balloonText": "[[value]]",
            "bullet": "round",
            "bulletBorderAlpha": 1,
         "bulletColor":"#FFFFFF",
            "hideBulletsCount": 50,
            "title": "red line",
            "valueField": "duration",
      "useLineColorForBulletBorder":true
        }],
        "chartScrollbar": {
            "autoGridCount": true,
            "graph": "g1",
            "scrollbarHeight": 40
        },
        "chartCursor": {
            "cursorPosition": "mouse"
        },
        "categoryField": "date",
        "categoryAxis": {
            "parseDates": true,
            "axisColor": "#DADADA",
            "dashLength": 1,
            "minorGridEnabled": true
        },
     "exportConfig":{
       menuRight: '20px',
          menuBottom: '30px',
          menuItems: [{
          icon: 'http://www.amcharts.com/lib/3/images/export.png',
          format: 'png'   
          }]  
     }
    });
    
    chart1.addListener("rendered", zoomChart);
    chart2.addListener("rendered", zoomChart);
    zoomChart();
    
    // this method is called when chart is first inited as we listen for "dataUpdated" event
    function zoomChart() {
        // different zoom methods can be used - zoomToIndexes, zoomToDates, zoomToCategoryValues
        chart1.zoomToIndexes(chartData.length - 40, chartData.length - 1);
        chart2.zoomToIndexes(chartData.length - 40, chartData.length - 1);
    }
    
    
    // generate some random data, quite different range
    function generateChartData() {
    
        
        var chartData = [];
        var firstDate = new Date();
        firstDate.setDate(firstDate.getDate() - 5);
        
        var myStats = JSON.parse(atob(stats));
        
        myStats.forEach(function(el, i) {
            chartData.push({
                date: new Date(el.date_unix * 1000),
                duration: el.average_listening,
                listeners: el.listeners
            });
        });

        return chartData;
    }
});


