<?php echo $header; ?>
<?php if ($this->ocw->getVersion() < 1.5) { ?>
	<?php if ($error_warning) { ?>	<div class="warning"><?php echo $error_warning; ?></div><?php } ?>
	<?php if ($success) { ?><div class="success"><?php echo $success; ?></div><?php } ?>
	<div class="box">
		<div class="left"></div>
		<div class="right"></div>
		<div class="heading">
			<h1 style="background-image: url('view/image/product.png');"> <?php echo $heading_title; ?></h1>
<?php } else { ?>
	<div id="content">
		<div class="breadcrumb">
			<?php foreach ($breadcrumbs as $breadcrumb) { ?>
				<?php echo $breadcrumb['separator']; ?>
				<a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
			<?php } ?>
		</div>
		<?php if ($error_warning) { ?><div class="warning"><?php echo $error_warning; ?></div><?php } ?>
		<?php if ($success) { ?><div class="success"><?php echo $success; ?></div><?php } ?>
		<div class="box">
			<div class="heading">
				<h1><img src="view/image/product.png" alt=""/> <?php echo $heading_title; ?></h1>
<?php } ?>
        <div class="buttons"><a onclick="location = '<?php echo $settings; ?>'" class="button"><span><?php echo $button_settings; ?></span></a><a onclick="location = '<?php echo $insert; ?>'" class="button"><span><?php echo $button_insert; ?></span></a><a onclick="copySubmit();" class="button"><span><?php echo $button_copy; ?></span></a><a onclick="deleteSubmit();" class="button"><span><?php echo $button_delete; ?></span></a></div>
  </div>
  <div class="content">
  	<div id="tabs" class="htabs">
  		<?php foreach ($stores as $store) { ?>
  		<a <?php echo ($this->ocw->getVersion() < 1.5) ? 'tab' : 'href'; ?>="#tab_store_<?php echo $store['store_id']; ?>" id="store_<?php echo $store['store_id']; ?>"><?php echo $store['name']; ?></a>
  		<?php } ?>
  	</div>
  	<?php foreach ($stores as $store) { ?>
  	<div id="tab_store_<?php echo $store['store_id']; ?>">
	    <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form_<?php echo $store['store_id']; ?>">
	      <table class="list display" width="100%" id="productTable_<?php echo $store['store_id']; ?>">
	        <thead>
	          <tr>
	            <th style="text-align: center;"><input type="checkbox" id="toggleCheckbox_<?php echo $store['store_id']; ?>" onclick="toggleSelect()" /></th>
	            <?php foreach ($columns as $column) {
					if ($column['key'] != 'selected') {
						echo '<th class="left">' . ${'column_'.$column['key']} . '</th>' . "\n";
					}
				} ?>
	          </tr>
	        </thead>
	        <tbody>
	          <tr>
	            <td class="center" style="height: 150px;" colspan="<?php echo count($columns); ?>" class="dataTables_empty">&nbsp;</td>
	          </tr>
	        </tbody>
	      </table>
	      <div class="clear">&nbsp;</div>
	    </form>
	 </div>
	 <?php } ?>
  </div>
</div>
<?php if ($this->ocw->getVersion() < 1.5) { ?></div><?php } ?>
<script type="text/javascript"><!--

var forceNewData = false;

function fnSetKey( aoData, sKey, mValue )
{
	for ( var i=0, iLen=aoData.length ; i<iLen ; i++ )
	{
		if ( aoData[i].name == sKey )
		{
			aoData[i].value = mValue;
		}
	}
}

function fnGetKey( aoData, sKey )
{
	for ( var i=0, iLen=aoData.length ; i<iLen ; i++ )
	{
		if ( aoData[i].name == sKey )
		{
			return aoData[i].value;
		}
	}
	return null;
}

function fnUpdateCacheColumn(oCache, row, col, data) {
	oCache.lastJson.aaData[row][col] = data;
}

function fnUpdateCallback(oTable, oCache, self, sValue, y) {
	var aPos = oTable.fnGetPosition( self );
	var result = $.parseJSON(sValue);
	var oSettings = oTable.fnSettings();
	var page = Math.ceil((oSettings._iDisplayStart - oCache.iCacheLower) / oSettings._iDisplayLength);
	var mod = ((page+1) % <?php echo $iPipe; ?>) - 1;
	var cachePage = (mod < 0) ? <?php echo $iPipe; ?> - 1 : mod;
	var cacheRow = (cachePage * oSettings._iDisplayLength) + aPos[0];
	var row = aPos[0];
	var col = aPos[2];
	
	if (result.error) {		
		oTable.fnUpdate( result.data, row, col, false );
		alert(result.error);
	} else {
		if (result.data != undefined) {
			fnUpdateCacheColumn(oCache, cacheRow, col, result.data);
			oTable.fnUpdate( result.data, row, col, false );
      <?php if ($this->ocw->getVersion() >= 1.5) { ?>
			if (result.data.indexOf("|") >= 0)
			{
				// Update Before Tax and After Tax prices
				var priceParts = result.data.split('|');
				var priceBeforeTax = priceParts[0];
				var priceAfterTax = priceParts[1];
				fnUpdateCacheColumn(oCache, cacheRow, <?php echo $priceIndex; ?>, priceBeforeTax);
				oTable.fnUpdate( priceBeforeTax, row, <?php echo $priceIndex; ?>, false );
				fnUpdateCacheColumn(oCache, cacheRow, <?php echo $priceAfterTaxIndex; ?>, priceAfterTax);
				oTable.fnUpdate( priceAfterTax, row, <?php echo $priceAfterTaxIndex; ?>, false );
			}
      <?php } ?>
			// Update Date Modified
			fnUpdateCacheColumn(oCache, cacheRow, <?php echo $modifiedIndex; ?>, result.timestamp);
			oTable.fnUpdate( result.timestamp, row, <?php echo $modifiedIndex; ?>, false );
		}
	}		
}

<?php foreach ($stores as $store) { ?>
var oCache_<?php echo $store['store_id']; ?> = {
		iCacheLower: -1
	};

function fnDataTablesPipeline_<?php echo $store['store_id']; ?> ( sSource, aoData, fnCallback ) {
	var iPipe = <?php echo $iPipe; ?>; /* Ajust the pipe size */
	
	var bNeedServer = false;
	var sEcho = fnGetKey(aoData, "sEcho");
	var iRequestStart = fnGetKey(aoData, "iDisplayStart");
	var iRequestLength = fnGetKey(aoData, "iDisplayLength");
	var iRequestEnd = iRequestStart + iRequestLength;
	oCache_<?php echo $store['store_id']; ?>.iDisplayStart = iRequestStart;

	/* force new data? */
	if (forceNewData) {
		bNeedServer = true;
	}
	
	/* outside pipeline? */
	if ( oCache_<?php echo $store['store_id']; ?>.iCacheLower < 0 || iRequestStart < oCache_<?php echo $store['store_id']; ?>.iCacheLower || iRequestEnd > oCache_<?php echo $store['store_id']; ?>.iCacheUpper )
	{
		bNeedServer = true;
	}
	
	/* sorting etc changed? */
	if ( oCache_<?php echo $store['store_id']; ?>.lastRequest && !bNeedServer )
	{
		for( var i=0, iLen=aoData.length ; i<iLen ; i++ )
		{
			if ( aoData[i].name != "iDisplayStart" && aoData[i].name != "iDisplayLength" && aoData[i].name != "sEcho" )
			{
				if ( aoData[i].value != oCache_<?php echo $store['store_id']; ?>.lastRequest[i].value )
				{
					bNeedServer = true;
					break;
				}
			}
		}
	}
			
	/* Store the request for checking next time around */
	oCache_<?php echo $store['store_id']; ?>.lastRequest = aoData.slice();
			
	if ( bNeedServer )
	{
		if ( iRequestStart < oCache_<?php echo $store['store_id']; ?>.iCacheLower )
		{
			iRequestStart = iRequestStart - (iRequestLength*(iPipe-1));
			if ( iRequestStart < 0 )
			{
				iRequestStart = 0;
			}
		}
		
		oCache_<?php echo $store['store_id']; ?>.iCacheLower = iRequestStart;
		oCache_<?php echo $store['store_id']; ?>.iCacheUpper = iRequestStart + (iRequestLength * iPipe);
		oCache_<?php echo $store['store_id']; ?>.iDisplayLength = fnGetKey( aoData, "iDisplayLength" );
		fnSetKey( aoData, "iDisplayStart", iRequestStart );
		fnSetKey( aoData, "iDisplayLength", iRequestLength*iPipe );
		
		$.getJSON( sSource, aoData, function (json) { 
			/* Callback processing */
			oCache_<?php echo $store['store_id']; ?>.lastJson = jQuery.extend(true, {}, json);
			
			if ( oCache_<?php echo $store['store_id']; ?>.iCacheLower != oCache_<?php echo $store['store_id']; ?>.iDisplayStart )
			{
				json.aaData.splice( 0, oCache_<?php echo $store['store_id']; ?>.iDisplayStart-oCache_<?php echo $store['store_id']; ?>.iCacheLower );
			}
			json.aaData.splice( oCache_<?php echo $store['store_id']; ?>.iDisplayLength, json.aaData.length );

			fnCallback(json)
		} );
		forceNewData = false;
	}
	else
	{
		json = jQuery.extend(true, {}, oCache_<?php echo $store['store_id']; ?>.lastJson);
		json.sEcho = sEcho; /* Update the echo for each response */
		json.aaData.splice( 0, iRequestStart-oCache_<?php echo $store['store_id']; ?>.iCacheLower );
		json.aaData.splice( iRequestLength, json.aaData.length );
		fnCallback(json);
		return;
	}
}
<?php } ?>

var productTables = {};

<?php foreach ($stores as $store) { ?>
	var productTable_<?php echo $store['store_id']; ?> = new Object();
<?php } ?>



$(document).ready(function() {

<?php foreach ($stores as $store) { ?>
	productTable_<?php echo $store['store_id']; ?> = $('#productTable_<?php echo $store['store_id']; ?>').dataTable({
		"sDom": 'f<"filterLink">lTC<"clear"><"filterDiv">riptip',
		"oTableTools": {
			"sSwfPath": "view/javascript/jquery/dataTables/swf/copy_cvs_xls.swf",			
			"aButtons": [
			 	"copy",
			 	"csv",
			 	{
				 	"sExtends": "print",
				 	"sInfo": "",
				 	"bShowAll": false,
				 	"sMessage": "<?php echo $text_print_instructions; ?>",
				 	"aButtons": [ "print" ]
			 	}	 					 	
			 ]
		},
		"oLanguage": {
			"sLengthMenu": "<?php echo $text_results_per_page; ?>",
			"sSearch": "<?php echo $text_search; ?>"
		},
		"aoColumnDefs": [
		    <?php if (count($bVisible)) { echo '{ "bVisible": false, "aTargets": [' . $bVisible . '] },' . "\n"; } ?>
		    <?php if (count($bSortable)) { echo '{ "bSortable": false, "aTargets": [' . $bSortable . '] },' . "\n"; } ?>
		    <?php foreach ($sWidth as $value) { echo '{ "sWidth": "' . $value['value'] . '", "aTargets": [ ' . $value['index'] . ' ] },' . "\n"; } ?>
			{ 
				"fnRender": function (obj) { return '<img src="' + obj.aData[<?php echo $imageIndex; ?>] + '" style="padding: 1px; border: 1px solid #DDDDDD;" />'; },
				"bUseRendered": false,
				"aTargets": [ <?php echo $imageIndex; ?> ]
			},
			{ 
				"fnRender": function (obj) { return '<input type="checkbox" name="selected[]" value="' + obj.aData[<?php echo $selectedIndex; ?>] + '" />'; },
				"aTargets": [ <?php echo $selectedIndex; ?> ]
			},
			{ 
				"fnRender": function (obj) { return '[ <a href="' + obj.aData[<?php echo $actionIndex; ?>][0].href + '">' + obj.aData[<?php echo $actionIndex; ?>][0].text + '</a> ]'; },
				"aTargets": [ <?php echo $actionIndex; ?> ]
			}				
		],
		"aoColumns": [
			<?php foreach ($columns as $i => $column) {
				if ($column['edittype'] != false) { 
					echo '{ "sClass": "' . $column['edittype'] . ' ' . $column['edittype'] . '_' . $column['key'] . '" }';
				} else {
					echo 'null';
				} 
				echo ($i == count($columns)-1) ? '' . "\n" : ',' . "\n";
			} ?>
		],
		"oColVis": { 
			"aiExclude": [ <?php echo $selectedIndex; ?> ],
			"buttonText": "<?php echo $text_toggle_columns; ?>"
		},
		"sPaginationType": "full_numbers",
		"iDisplayLength": 10,
		"aLengthMenu": [[10, 25, 50], [10, 25, 50]],
		"bAutoWidth": false,
		"bStateSave": true,
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "index.php?route=catalog/product_manager/getDataTable&store_id=<?php echo $store['store_id']; ?>&token=<?php echo $token; ?>",
		"fnServerData": fnDataTablesPipeline_<?php echo $store['store_id']; ?>,
		"fnRowCallback": function(nRow, aData, iDisplayIndex) {
			$(nRow).attr("id", aData[<?php echo $idIndex; ?>]);
			return nRow;
		},
		"fnDrawCallback": function() {
			$('td.input', productTable_<?php echo $store['store_id']; ?>.fnGetNodes()).editable( 'index.php?route=catalog/product_manager/editDataTable&store_id=<?php echo $store['store_id']; ?>&token=<?php echo $token; ?>', {
				"callback": function( sValue, y ) {
					fnUpdateCallback(productTable_<?php echo $store['store_id']; ?>, oCache_<?php echo $store['store_id']; ?>, this, sValue, y);
				},
				"submitdata": function ( value, settings ) {
					var aPos = productTable_<?php echo $store['store_id']; ?>.fnGetPosition( this );
					return {
						"row_id": this.parentNode.getAttribute('id'),
						"column": aPos[2],
						"old_value": productTable_<?php echo $store['store_id']; ?>.fnGetData(aPos[0])[aPos[2]]
					};
				},
				"indicator": "<?php echo $text_indicator_saving; ?>",
				"placeholder": "<?php echo $text_none; ?>",
				"height": "14px",
				"onblur": "submit",
				"onsubmit": function (settings, original) {
					if (original.revert == $('input',this).val() || (original.revert == '' && $('input',this).val() == "<?php echo $text_none; ?>")) {
						original.reset();
						return false;
					}
				}
			});
			<?php foreach ($columns as $i => $column) { ?>
			<?php if ($column['edittype'] == 'select') { ?>
			$('td.select_<?php echo $column['key']; ?>', productTable_<?php echo $store['store_id']; ?>.fnGetNodes()).editable( 'index.php?route=catalog/product_manager/editDataTable&store_id=<?php echo $store['store_id']; ?>&token=<?php echo $token; ?>', {
				"callback": function( sValue, y ) {
					fnUpdateCallback(productTable_<?php echo $store['store_id']; ?>, oCache_<?php echo $store['store_id']; ?>, this, sValue, y);
				},
				"submitdata": function ( value, settings ) {
					var aPos = productTable_<?php echo $store['store_id']; ?>.fnGetPosition( this );
					return {
						"row_id": this.parentNode.getAttribute('id'),
						"column": aPos[2],
						"old_value": productTable_<?php echo $store['store_id']; ?>.fnGetData(aPos[0])[aPos[2]]
					};
				},
				"type": "select",
				"data": <?php echo $column['options']; ?>,
				"indicator": "<?php echo $text_indicator_saving; ?>",
				"placeholder": "<?php echo $text_none; ?>",
				"onblur": "submit",
				"onsubmit": function (settings, original) {
					if (original.revert == $('select option:selected',this).text() || (original.revert == '' && $('select option:selected',this).text() == "<?php echo $text_none; ?>")) {
						original.reset();
						return false;
					}
				}
			});
			<?php } ?>			
			<?php } ?>
		}		
	});

	productTables['store_<?php echo $store['store_id']; ?>'] = productTable_<?php echo $store['store_id']; ?>;
<?php } ?>

	$('.filterLink').html('<a onclick="toggleFilterDiv()"><?php echo $text_advanced_filter; ?></a>');
	<?php if (!count($filter_category)) { ?>
	$('.filterDiv').hide();
	$('.filterDiv').addClass('isClosed');
	<?php } else { ?>
	$('.filterDiv').addClass('isOpen');
	<?php } ?>
	$('.filterDiv').html('<p><b><?php echo $entry_filter_category; ?></b></p><span> \
	<a onclick="$(this).parent().find(\':checkbox\').attr(\'checked\', true);"><?php echo $text_select_all; ?></a> / <a onclick="$(this).parent().find(\':checkbox\').attr(\'checked\', false);"><?php echo $text_select_none; ?></a></td> \
	<div class="filter_scrollbox filter_category"> \
	    <?php $class = 'odd'; ?> \
	    <?php foreach ($categories as $category) { ?> \
	    <?php $class = ($class == 'even' ? 'odd' : 'even'); ?> \
	    <div class="<?php echo $class; ?>"><label> \
	      <?php if (in_array($category['category_id'], $filter_category)) { ?> \
	      <input type="checkbox" class="filter_category_checkbox" name="filter_category[]" value="<?php echo $category['category_id']; ?>" checked="checked" /> \
	      <?php echo addslashes($category['name']); ?> \
	      <?php } else { ?> \
	      <input type="checkbox" class="filter_category_checkbox" name="filter_category[]" value="<?php echo $category['category_id']; ?>" /> \
	      <?php echo addslashes($category['name']); ?></label> \
	      <?php } ?> \
	    </div> \
	    <?php } ?> \
	  </div> \
	  <p><a onclick="applyFilter();" class="button"><span><?php echo $button_apply_filter; ?></span></a> <span class="filterLoading"></span></p> \
	  ');  	

	$('.filter_category_checkbox').change(function() {
		var value = $(this).attr('value');
		var checked = $(this).attr('checked');
		$('.filter_category_checkbox').each(function() {
			if ($(this).attr('value') == value) {
				$(this).attr('checked', checked);
			}
		});
	});
});

function toggleFilterDiv() {
	if ($('.filterDiv').hasClass('isOpen')) {
		$('.filterDiv').hide();
		$('.filterDiv').removeClass('isOpen');
	} else {
		$('.filterDiv').addClass('isOpen');
		$('.filterDiv').show();
	}
}
function applyFilter() {
	var store = $('#tabs .selected').attr('id').split('_')[1];
	
	$('.filterLoading').html('<?php echo $text_indicator_saving; ?>');
	
	filter_category = '';
	$('.filter_category_checkbox').each(function() {
		if ($(this).attr('checked')) {
			filter_category += $(this).attr('value') + '|';
		}
	});

	$.ajax({
		url: 'index.php?route=catalog/product_manager/apply_filter&token=<?php echo $token; ?>',
		type: 'POST',
		data: 'filter_category=' + encodeURIComponent(filter_category),
		dataType: 'json',
		success: function(data) {
			<?php foreach ($stores as $store) { ?>
			forceNewData = true;
			productTables['store_<?php echo $store['store_id']; ?>'].fnDraw();
			<?php } ?>
			$('.filterLoading').html('');
		}
	});
}
//--></script>
<script type="text/javascript"><!--
function toggleSelect() {
	var store = $('#tabs .selected').attr('id').split('_')[1];
	$('#form_'+store+' input[name*=\'selected\']').attr('checked', $('#toggleCheckbox_'+store).attr('checked'));
}

function deleteSubmit() {
	var store = $('#tabs .selected').attr('id').split('_')[1];

	var hasSelection = false;
	$('#form_'+store+' input[name*=\'selected\']').each(function() {
		if ($(this).attr('checked')) hasSelection = true;
	});

	if (hasSelection) {
		if (!confirm ('<?php echo $text_confirm_delete; ?>')) {
	        return false;
	    } else { 
			$('#form_'+store).submit();
	    }
	} else {
		alert('<?php echo $error_no_selection; ?>');
		return false;
	}
}

function copySubmit() {
	var store = $('#tabs .selected').attr('id').split('_')[1];

	var hasSelection = false;
	$('#form_'+store+' input[name*=\'selected\']').each(function() {
		if ($(this).attr('checked')) hasSelection = true;
	});

	if (hasSelection) {
		$('#form_'+store).attr('action', '<?php echo str_replace('&amp;','&', $copy); ?>');
	    $('#form_'+store).submit();
	} else {
		alert('<?php echo $error_no_selection; ?>');
		return false;
	}	
}

//--></script>
<script type="text/javascript"><!--
<?php if ($this->ocw->getVersion() < 1.5) { ?>
$.tabs('.htabs a');
<?php } else { ?>
$('.htabs a').tabs();
<?php } ?>
//--></script>
<?php echo $footer; ?>