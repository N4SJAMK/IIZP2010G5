$(function() {
  $(".datepicker").datepicker({
    dateFormat: "dd.mm.yy",
    firstDay: 1,
    showOtherMonths: true,
    selectOtherMonths: true
  });
});

function check(caller, toggle) {
  if(caller === undefined) return;
  if(!$(caller).is(":checked")) deselectSelectAll();
  if(toggle == "ban-button") toggleBanButton();
}

function switchDropdown() {
  $("#selection-dropdown-switch").toggleClass("open");
}

function deselectSelectAll() {
  $("[name='selection-all']").prop("checked", false);
}

function selectAll(check) {
  if(check === undefined) return;
  $("[name='selection']").prop("checked", check);
  if(check == 2) $("[name='selection-all']").prop("checked", true);
  else deselectSelectAll();
}

function selectionError() {
  $("[name='selection-all'] + label").addClass("error");
  $("[name='selection'] + label").addClass("error");
  setTimeout(function() {
    $("[name='selection-all'] + label").removeClass("error");
    $("[name='selection'] + label").removeClass("error");
  }, 500);
}

function toggleBanButton() {
  var checked = $("[name='selection']:checked").length;
  if(checked > 0 && checked == $("[name='selection']:checked").parents("tr.banned").length) {
    $("#ban-button")
      .attr("id", "unban-button")
      .attr("name", "unban_user")
      .removeClass("red")
      .addClass("purple")
      .html("<i class=\"fa fa-gavel fa-lg fa_fix\"></i> Unban");
  } else {
    $("#unban-button")
      .attr("id", "ban-button")
      .attr("name", "ban_user")
      .removeClass("purple")
      .addClass("red")
      .html("<i class=\"fa fa-gavel fa-lg fa_fix\"></i> Ban");
  }
}

// ========== Lightbox ==========

var lockLightbox = false;
var reloadPage = false;

function closeLightbox() {
  if(lockLightbox) return;
  $("#lightbox-container").removeClass("visible");
  $("#lightbox-loader").empty();
  if(reloadPage) location.reload(true);
}

function loadLightbox(caller, maxSelection) {
  var type = $(caller).attr("name");
  if(maxSelection === undefined || maxSelection > 0) {
    var selectAllCheckbox = $("[name='selection-all']");
    if($(selectAllCheckbox).prop("checked") && $(selectAllCheckbox).prop("value") == "allResults") {
      selected = $("[name='filter_results']").val();
      if(selected == 0 || (maxSelection !== undefined && selected > maxSelection)) {
        selectionError();
        return;
      }
    } else {
      var selected = Array();
      $("[name='selection']:checked").each(function() {
        selected.push($(this).val());
      });
      if(selected.length == 0 || (maxSelection !== undefined && selected.length > maxSelection)) {
        selectionError();
        return;
      }
    }
  }
  $("#lightbox-container").addClass("visible");
  $("#lightbox-loader").html("<div class=\"loading\"><i class=\"fa fa-spinner fa-spin\"></i></div>");
  $("#lightbox-loader .loading").fadeIn(1000);
  $.ajax({
    type: "POST",
    url: "lightbox.php",
    data: {type: type, selected: selected}, 
    complete: function(jqXHR, textStatus) {
      if(textStatus == "success" && jqXHR.responseText != "") $("#lightbox-loader").html(jqXHR.responseText);
      else {
        $("#lightbox-container").removeClass("visible");
        $("#lightbox-loader").empty();
      }
    }
  });
}

function sendFormData(caller) {
  lockLightbox = true;
  var dataToPost = {};
  dataToPost.type = $(caller).attr("name");
  var selectAllCheckbox = $("[name='selection-all']");
  if($(selectAllCheckbox).prop("checked") && $(selectAllCheckbox).prop("value") == "allResults") {
    $(".prev-search").each(function() {
      dataToPost[$(this).attr("name")] = $(this).val();
    });
  } else {
    var selected = Array();
    $("[name='selection']:checked").each(function() {
      selected.push($(this).val());
    });
    dataToPost.selected = selected;
  }
  $(".form-addition").each(function() {
    if($(this).attr("type") != "checkbox" || $(this).prop("checked")) dataToPost[$(this).attr("name")] = $(this).val();
  });
  $("#lightbox-content").html("<div class=\"loading\"><i class=\"fa fa-spinner fa-spin\"></i></div>");
  $.ajax({
    type: "POST",
    url: "formhandler.php",
    data: {jsonData: JSON.stringify(dataToPost)}, 
    complete: function(jqXHR, textStatus) {
      if(textStatus == "success" && jqXHR.responseText != "") $("#lightbox-content").html(jqXHR.responseText);
      else $("#lightbox-content").html("<p>Error! Please try again.</p>");
      $("#lightbox-footer").html("<button type=\"button\" class=\"blue\" onclick=\"closeLightbox()\">Ok</button>");
      reloadPage = true;
      lockLightbox = false;
    }
  });
}
