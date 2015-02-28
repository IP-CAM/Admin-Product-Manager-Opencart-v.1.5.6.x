var categoryManagerTreeSelector = "#category_manager_tree";
var categoryManagerThemePath = 'view/javascript/jquery/jstree-1.0/themes/default/';
var categoryManagerCurrentRollback = [];
var categoryManagerHasChanges = false;
var categoryManagerSetting_dblclick_to_edit = true;
var categoryManangerNewNodeCount = 0;

$(function()
{


  $(categoryManagerTreeSelector).jstree({
    "core" : { "animation" : 200 },
    "json_data" : {
      "ajax" : {
        "url" : "index.php?route=catalog/category_manager/get_json_category_list&token=" + token,
        "data" : function(n)
        {
          return { id : n.attr ? n.attr("id") : 0 }
        }
      }
    },
    types : {
      types : {
        "enabled" : {
          icon : {
            image : categoryManagerThemePath + "folder_enabled.png"
          }
        },
        "disabled" : {
          icon : {
            image : categoryManagerThemePath + "folder_disabled.png"
          }
        }
      }
    },
    "dnd" : {
      "copy_modifier" : false
    },
    "checkbox" : {
      two_state : true,
      real_checkboxes : true,
      real_checkboxes_names : function (n) { return ["selected[]", (n[0].id.replace("node_","") || 0)]; }
    },
    "contextmenu" : {
      "items" : {
        "create" : {
					"separator_before"	: false,
					"separator_after"	: true,
					"label"				: "Create",
					"action"			: function (obj) { this.create(obj); }
				},
				"rename" : {
					"separator_before"	: false,
					"separator_after"	: false,
					"label"				: "Rename",
					"action"			: function (obj) { this.rename(obj); }
				},
				"remove" : {
					"separator_before"	: false,
					"icon"				: false,
					"separator_after"	: false,
					"label"				: "Delete",
					"action"			: function (obj) {
            if (confirm("Are you sure you want to delete the selected category(s) and all of their subcategories?")) {
              if(this.is_selected(obj)) { this.remove(); } else { this.remove(obj); }
            }
          }
				},
        "enable" : {
					"separator_before"	: true,
					"separator_after"	: false,
					"label"				: "Enable",
					"action"			: function (obj) {
            enableCategory(obj.attr('category_id').replace("node_", ""));
          }
				},
        "disable" : {
					"separator_before"	: false,
					"separator_after"	: false,
					"label"				: "Disable",
					"action"			: function (obj) {
            disableCategory(obj.attr('category_id').replace("node_", ""));
          }
				},
        "edit" : {
					"separator_before"	: true,
					"separator_after"	: false,
					"label"				: "Edit Details",
					"action"			: function (obj) {
            showCategoryDetails(obj.attr('category_id').replace("node_", ""));
          }
				},
				"ccp" : false
      }
    },
    "plugins" : [ "themes","json_data","ui","crrm","cookies","dnd","types","checkbox", "hotkeys","contextmenu"]
  })
  .bind("loaded.jstree", function (e, data)
  {
    if (categoryManagerSetting_ExpandOnload)
      $(categoryManagerTreeSelector).jstree("open_all");
  })
  .bind("dblclick.jstree", function(e)
  {
    if (categoryManagerSetting_dblclick_to_edit) {
      var category_id = $(e.target).closest("li").attr("category_id").replace("node_", "");
      showCategoryDetails(category_id);
    } else {
      $(categoryManagerTreeSelector).jstree("toggle_node", $(e.target).closest("li"));
    }
  })
  .bind("rename.jstree", function (e, data)
  {
    showApplyChanges();
    categoryManagerCurrentRollback.push(data.rlbk);
  })
  .bind("check_node.jstree", function(e)
  {
    $('.delete_btn').button("option", "disabled", false);
    $('.delete_btn').unbind('click').bind('click', function()
    {
      deleteSelectedNodes();
    });
  })
  .bind("uncheck_node.jstree", function(e)
  {
    if ($('.jstree-checked').length == 0) {
      $('.delete_btn').button("option", "disabled", true);
      $('.delete_btn').unbind('click');
    }
  })
  .bind("create.jstree", function (e, data)
  {
    showApplyChanges();
    categoryManagerCurrentRollback.push(data.rlbk);
    $(data.rslt.obj).attr("category_id", "node__"+categoryManangerNewNodeCount);
    $(data.rslt.obj).attr("id", "node__"+categoryManangerNewNodeCount);
    categoryManangerNewNodeCount++;
    if (categoryManagerSetting_DefaultStatus)
      enableCategory($(data.rslt.obj).attr('category_id').replace("node_", ""));
    else
      disableCategory($(data.rslt.obj).attr('category_id').replace("node_", ""));
  })
  .bind("remove.jstree", function (e, data)
  {
    showApplyChanges();
    categoryManagerCurrentRollback.push(data.rlbk);
  })
  .bind("move_node.jstree", function (e, data)
  {
    showApplyChanges();
    categoryManagerCurrentRollback.push(data.rlbk);
  });
});

function getTreeData()
{
  var sort_order = 0;
  var data = [];
  $('li', $(categoryManagerTreeSelector).jstree("get_container")).each(function()
  {
    var name = $('a:first', this).text().trim();
    var category_id = $(this).attr('category_id').replace("node_", "");
    var parent_id = $(this).parent().parent().attr('category_id') != null ? $(this).parent().parent().attr('category_id').replace("node_", "") : 0;
    var status = $(this).attr('rel') == 'enabled' ? 1 : 0;

    var item = {
      name : name,
      category_id : category_id,
      parent_id : parent_id,
      sort_order : sort_order,
      status : status
    };
    data.push(item);

    //console.log('[name:' + name + ', category_id: ' + category_id + ', parent_id: ' + parent_id + ', sort_order: ' + sort_order + ', status: ' + status + ']');

    sort_order++;
  });

  return data;
}

function showApplyChanges()
{
  setStatusAsChanged();

  $('#apply_changes').removeClass("success").removeClass("warning").addClass("attention");
  $('#apply_changes').html('Status: <strong>Changes Detected</strong> &mdash; Your changes have <strong>not</strong> been saved yet.');
}

function setStatusAsChanged()
{
  categoryManagerHasChanges = true;
  $('.save_btn').button("option", "disabled", false);
  $('.undo_btn').button("option", "disabled", false);
  $('.undo_btn').unbind('click').bind('click', function()
  {
    undoLastChange();
    $(this).removeClass('ui-state-hover');
  });
  $('.save_btn').unbind('click').bind('click', function()
  {
    doApplyChanges(null);
    $(this).removeClass('ui-state-hover');
    $('.undo_btn, .save_btn').button("option", "disabled", true);
    $('.undo_btn, .save_btn').unbind('click');
  });
}

function setStatusAsUnchanged()
{
  categoryManagerHasChanges = false;
  $('.undo_btn, .save_btn').button("option", "disabled", true);
  $('.undo_btn, .save_btn').unbind('click');
}


function undoLastChange()
{
  var rlbk = categoryManagerCurrentRollback.pop();
    $.jstree.rollback(rlbk);
    if (categoryManagerCurrentRollback.length == 0)
    {
      doNoMoreChanges();
    }
}

function doNoMoreChanges()
{
  setStatusAsUnchanged();
  $('#apply_changes').removeClass("attention").removeClass("warning").addClass("success");
  $('#apply_changes').html(categoryManagerReadyText);
}

function doApplyChanges(callback)
{
  $("#apply_changes").html('Status: <strong>Saving...</strong>');
  $.ajax({
    url : "index.php?route=catalog/category_manager/save_tree&token=" + token,
    type : 'POST',
    dataType : 'json',
    data : { data : JSON.stringify(getTreeData()) },
    success : function(response)
    {
      if (response.error)
      {
        $('.warning').html(response.error);
        return;
      }
      if (response.success)
        doSaveSuccess(callback);
    }
  });
}

function doSaveSuccess(callback)
{
  setStatusAsUnchanged();
  categoryManagerCurrentRollback = [];

  $("#apply_changes").removeClass('attention').addClass('success').html('Status: <strong>Save Successful!</strong>');
  setTimeout('doNoMoreChanges();', 2000);
  if (callback)
    setTimeout(callback, 1000);
}

function discardTreeChanges()
{
  categoryManagerCurrentRollback = [];
  doNoMoreChanges();
  $(categoryManagerTreeSelector).jstree("refresh");
}

function insertCategory()
{
  if (categoryManagerHasChanges)
  {
    if (!confirm("There are changes pending, are you sure you want to discard them and leave this page?"))
    {
      return;
    }
  }

  location = categoryManagerInsertUrl;
}

function enableCategory(category_id)
{
  categoryManagerCurrentRollback.push($(categoryManagerTreeSelector).jstree("get_rollback"));
  $(categoryManagerTreeSelector).jstree("set_type","enabled", "#node_" + category_id);
  showApplyChanges();
}

function disableCategory(category_id)
{
  categoryManagerCurrentRollback.push($(categoryManagerTreeSelector).jstree("get_rollback"));
  $(categoryManagerTreeSelector).jstree("set_type","disabled", "#node_" + category_id);
  showApplyChanges();
}

function deleteSelectedNodes()
{
  if ($('.jstree-checked').length > 0) {
    if (confirm("Are you sure you want to delete the checked category(s) and all of their subcategories?"))
    {
      $(categoryManagerTreeSelector).jstree("remove",".jstree-checked");
    }
  }
}

function showCategoryDetails(category_id)
{
  if (categoryManagerHasChanges)
  {
    $('#apply_changes_confirm').remove();
    $('body').prepend('<div id="apply_changes_confirm">You must save or discard your current changes before continuing. What would you like to do? </div>');
    $("#apply_changes_confirm").dialog({
      resizable: false,
      height: 140,
      width: 400,
      modal: true,
      buttons: {
        "Save & Continue" : function()
        {
          $(this).dialog("close");
          doApplyChanges("openCategoryDetailsDialog(" + category_id + ");");
        },
        "Discard & Continue" : function()
        {
          $(this).dialog("close");
          discardTreeChanges();
          openCategoryDetailsDialog(category_id);
        },
        "Cancel" : function()
        {
          $(this).dialog("close");
        }
      }
    });
  } else
  {
    openCategoryDetailsDialog(category_id);
  }
}

function collapseAll()
{
  $(categoryManagerTreeSelector).jstree("close_all");
}

function expandAll()
{
  $(categoryManagerTreeSelector).jstree("open_all");
}

function openCategoryDetailsDialog(category_id)
{
  $('#category_manager_details').remove();

  $('body').prepend('<div id="category_manager_details" style="display: none; padding: 3px 0px 0px 0px;"><iframe id="category_manager_details_iframe" src="about:blank" style="padding:0; margin: 0; display: block; width: 100%; height: 100%;" frameborder="no" scrolling="auto"></iframe></div>');

  var iframe = $('#category_manager_details iframe')[0];

  iframe.src = 'index.php?route=catalog/category/update&only_show_content=1&category_id=' + encodeURIComponent(category_id) + '&token=' + token;

  $('#category_manager_details').dialog({
    dialogClass : 'detailsDialog',
    title: 'Edit Category Details',
    bgiframe: false,
    width: 1000,
    position: 'center',
    buttons: {
      "Save" : function()
      {
        $('#category_manager_details_iframe').contents().find('#form').submit();
      },
      "Cancel": function()
      {
        $(this).dialog("close");
      }
    },
    height: 520,
    resizable: false,
    modal: false
  });
}

function getDialogButton(dialog_selector, button_name)
{
  var buttons = $(dialog_selector + ' .ui-dialog-buttonpane button');
  for (var i = 0; i < buttons.length; ++i)
  {
    var jButton = $(buttons[i]);
    if (jButton.text() == button_name)
    {
      return jButton;
    }
  }

  return null;
}

function handleCategoryDetailsSuccess()
{
  $('#category_manager_details').dialog("close");
  doSaveSuccess(null);
  $(categoryManagerTreeSelector).jstree("refresh");
}