(function($, Drupal, drupalSettings) {
  Drupal.behaviors.tableau_vis = {
    attach: function (context, settings) {
      /* Disable Enter Key */
      $(document).keypress( function(event){
        if (event.which == '13') {
          //event.preventDefault();
        }
      });
      var server = drupalSettings[0].tableau_vis.tableau_server;
      var target = drupalSettings[0].tableau_vis.tableau_target_site;
      var botblocker = drupalSettings[0].tableau_vis.tableau_bot_blocker;
      var breakpoint = drupalSettings[0].tableau_vis.tableau_breakpoint;
      var timeout = drupalSettings[0].tableau_vis.tableau_timeout;
      var visualization = drupalSettings[0].tableau_vis.tableau_visualization;
      var module_path = drupalSettings[0].tableau_vis.tableau_basepath;
      var timeout_bool = drupalSettings[0].tableau_vis.tableau_timeout_bool;
      var break_bool = drupalSettings[0].tableau_vis.tableau_break_bool;
      var back_bool = drupalSettings[0].tableau_vis.tableau_back_bool;
      var placeholder = drupalSettings[0].tableau_vis.tableau_placeholder_url;
      var idleTime = 0;
      var idleInterval = setInterval(timerIncrement, 1000);
      var lastWidth;
      var initialWidth = $(window).width();
      var deviceSize;
      var submitCount;
      var community;
      if (initialWidth >= breakpoint) {
        deviceSize = 'desktop';
      }
      else {
        deviceSize = 'phone';
      }
      if ($('#vizContainer').next('.honeypot-link-element').length > 0) {
        $('#vizContainer').next('.honeypot-link-element').hide();
      }
      var ticket;
      var serverCheck;
      var viz;
      var vizDiv = document.getElementById('vizContainer');
      $.ajax({
        type: "GET",
        url: "/tableau-vizualization/url-check",
        async: false,
        success : function(text) {
          serverCheck = text;
        }
      });

      if ($('#vizContainer').is(':empty') && (((typeof window != 'undefined') && (botblocker == 1)) || (botblocker == 0))) {
        $.ajax({
          type: "GET",
          url: "/tableau-vizualization/ticket",
          async: false,
          success : function(text) {
            ticket = text;
          }
        });
        if (serverCheck == 'true') {
          var vizURL = server + ticket + '/t/' + target + '/views/' + visualization + '?:embed=yes&:showVizHome=no&';
          var options = {
            hideToolbar: true,
            hideTabs: true,
            device: deviceSize,
          };

          viz = new tableau.Viz(vizDiv, vizURL, options);
          submitCount = 0;
        }
        else {
          vizDiv.innerHTML += "<img src='" + placeholder + "' alt='tableau visualization currently unavailable' />";
        }
      }
      $(window).resize(function(deviceSize) {
        var $window = $(this);
        var windowWidth = $(window).width();
        if(windowWidth <= breakpoint && lastWidth >= breakpoint && break_bool == 1) {
          if(viz){
            viz.dispose();
            vis_refresh('phone');
            console.log('refreshing');
          }
          else {
            window.location.reload();
          }
        }
        else if(windowWidth >= breakpoint && lastWidth <= breakpoint && break_bool == 1) {
          if(viz){
            viz.dispose();
            vis_refresh('desktop');
            console.log('refreshing');
          }
          else {
            window.location.reload();
          }
        }
        lastWidth = windowWidth;
      });
      $(window).mousemove(function (e) {
        idleTime = 0;
      });
      $(window).keypress(function (e) {
        idleTime = 0;
      });
      function timerIncrement() {
        idleTime = idleTime + 1;
        if (idleTime >= timeout && timeout_bool == 1) {
          var idleWidth = $(window).width();
          if (idleWidth <= breakpoint) {
            if(viz){
              viz.dispose();
              vis_refresh('phone');
              console.log('refreshing');
            }
            else {
              window.location.reload();
            }
          }
          else if (idleWidth >= breakpoint) {
            if(viz){
              viz.dispose();
              vis_refresh('desktop');
              console.log('refreshing');
            }
            else {
              window.location.reload();
            }
          }
          idleTime = 0;
        }
      }
      function getCommunity(zip) {
        $zip = zip;
        console.log($zip);
        $url = 'rest/tableau-select-communities.json?zip=' + $zip;
        var dropdown = $('#community-select');
        dropdown.empty();
        dropdown.prop('selectedIndex', 0);
        $.getJSON($url, function(jsonData) {
          $.each(jsonData, function(key,entry){
            $community_id = entry.value+'';
            $title = $community_id.split(' -')[0];
            dropdown.append($('<option></option>').attr('value', entry.value).text($title));
          });
        });
      }
      /* If filter exists: Make API calls based on filter */
      if ($('.tableau-view').length) {
        var tableauSearch = $('.tableau-view .form-type-textfield input');
        var tableauApply = $('.tableau-view .form-submit');
        var tableauReset = $('.tableau-view .edit-reset');
      }
      $('.tableau-filter').each(function () {
        var currentFilter = $(this).attr('id');
        var pName = currentFilter.slice(4);
        var pType = $('#'+currentFilter+' .type').val();
        var pSheet = $('#'+currentFilter+' .sheet').val();
        var pSelect = $('#'+pName);
        var pTag = document.getElementById(pName).tagName.toLowerCase();
        var workbook;
        switch (pType) {
          case 'Filter':
          case 'SubFilter':
            pSelect.change(function() {
              value = $('#'+pName).val();
              sheet = viz.getWorkbook().getActiveSheet().getWorksheets().get("pSheet");
              return sheet.applyFilterAsync(pName,value, tableau.FilterUpdateType.REPLACE);
            });
            break;
          case 'Parameter':
            if (pTag == 'select') {
              pSelect.change(function() {
                value = $('#'+pName).val();
                if(viz){
                  workbook = viz.getWorkbook();
                  workbook.changeParameterValueAsync(pName, value);
                  viz.refreshDataAsync();
                }
              });
            }
            else if (pTag == 'input') {
              dropdown = $('#community-select');
              submitButton = $('#tableau-submit');
              resetButton = $('#tableau-reset');
              resultText = $('#tableau-results');
              $('#'+pName).keypress(function(event) {
                if (event.keyCode == '13') {
                  if($('#'+pName).val() && dropdown.length) {
                    value = $('#'+pName).val();
                    $dropdownValue = dropdown.val();
                    $communityName = $dropdownValue.split(' -')[0];
                    tableauSearch.val($dropdownValue);
                    tableauApply.click();
                    if(viz){
                      workbook = viz.getWorkbook();
                      workbook.changeParameterValueAsync(pName, value);
                      workbook.changeParameterValueAsync('pCommunity', $communityName);
                      viz.refreshDataAsync();
                    }
                  }
                }
              });
              $('#'+pName, context).once(function() {
                $(this).on('input',function(e){
                  var valueLength = $('#'+pName).val().length;
                  value = $('#'+pName).val();
                  if(valueLength == 5) {
                    getCommunity(value);
                    dropdown.prop('disabled', false);
                  }
                });
              });
              submitButton.on('click', function(){
                if($('#'+pName).val() && dropdown.length) {
                  value = $('#'+pName).val();
                  $dropdownValue = dropdown.val();
                  $communityName = $dropdownValue.split(' -')[0];
                  tableauSearch.val($dropdownValue);
                  tableauApply.click();
                  if(viz){
                    workbook = viz.getWorkbook();
                    workbook.changeParameterValueAsync(pName, value);
                    workbook.changeParameterValueAsync('pCommunity', $communityName);
                    viz.refreshDataAsync();
                  }
                }
              });
              resetButton.on('click', function(){
                if(viz){
                  viz.refreshDataAsync();
                }
                else {
                  window.location.reload();
                }
              });
            }
            break;
          default:
        }
      });
      if(!!window.performance && window.performance.navigation.type === 2 && back_bool == 1)
      {
        window.location.reload();
      }
      function vis_refresh(device) {
        $.ajax({
          type: "GET",
          url: "/tableau-vizualization/ticket",
          async: false,
          success : function(text)
          {
            ticket = text;

          }
        });
        var options = {
          hideToolbar: true,
          hideTabs: true,
          device: device,
        };
        var vizURL = server + ticket + '/t/' + target + '/views/' + visualization + '?:embed=yes&:showVizHome=no&';
        viz = new tableau.Viz(vizDiv, vizURL, options);
      }

      $('#export-viz-pdf').click(function(){
        if (viz){
          viz.showExportImageDialog();
        }
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
