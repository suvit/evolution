<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if(!$modx->hasPermission('edit_module') && $_REQUEST['a']==106) {	
	$e->setError(3);
	$e->dumpError();	
}

// initialize page view state - the $_PAGE object
$modx->manager->initPageViewState();

// get and save search string
if($_REQUEST['op']=='reset') {
	$query = '';
	$_PAGE['vs']['search']='';
} 
else {
	$query = isset($_REQUEST['search'])? $_REQUEST['search']:$_PAGE['vs']['search'];
	$sqlQuery = mysql_escape_string($query);
	$_PAGE['vs']['search'] = $query;
}

// get & save listmode
$listmode = isset($_REQUEST['listmode']) ? $_REQUEST['listmode']:$_PAGE['vs']['lm'];
$_PAGE['vs']['lm'] = $listmode;   


// context menu
include_once $base_path."manager/includes/controls/contextmenu.php";
$cm = new ContextMenu("cntxm", 150);
$cm->addItem($_lang["run_module"],"js:menuAction(1)","media/images/icons/save.gif",(!$modx->hasPermission('exec_module') ? 1:0));
$cm->addSeparator();
$cm->addItem($_lang["edit"],"js:menuAction(2)","media/images/icons/logging.gif",(!$modx->hasPermission('edit_module') ? 1:0));
$cm->addItem($_lang["duplicate"],"js:menuAction(3)","media/images/icons/newdoc.gif",(!$modx->hasPermission('new_module') ? 1:0));
$cm->addItem($_lang["delete"], "js:menuAction(4)","media/images/icons/delete.gif",(!$modx->hasPermission('delete_module') ? 1:0));
echo $cm->render();

?>

<div class="subTitle">
	<span class="right"><img src="media/images/_tx_.gif" width="1" height="5"><br /><?php echo $_lang['module_management']; ?></span>
</div>
<script language="JavaScript" type="text/javascript">
  	function searchResource(){
		document.resource.op.value="srch";
		document.resource.submit();
	};
	
	function resetSearch(){
		document.resource.search.value = ''
		document.resource.op.value="reset";
		document.resource.submit();
	};

	function changeListMode(){
		var m = parseInt(document.resource.listmode.value) ? 1:0;
		if (m) document.resource.listmode.value=0;
		else document.resource.listmode.value=1;
		document.resource.submit();
	};
	
	var selectedItem;
	var contextm = <?php echo $cm->getClientScriptObject(); ?>;
	function showContentMenu(id,e){
		var x,y,st = document.getScrollTop();
		x = e.clientX>0 ? e.clientX:e.pageX;
		y = e.clientY>0 ? e.clientY:e.pageY;
		selectedItem=id;
		if (((y+contextm.getHeight())-10)>document.getHeight()) y=document.getHeight() - contextm.getHeight();
		contextm.setLocation(x+5,y+st-10);
		contextm.setVisible(true);
		e.cancelBubble=true;
		return false;
	};
	function menuAction(a) {
		var id = selectedItem;
		switch(a) {
			case 1:		// run module
				dontShowWorker = true; // prevent worker from being displayed
				window.location.href='index.php?a=112&id='+id;
				break;			
			case 2:		// edit
				window.location.href='index.php?a=108&id='+id;
				break;			
			case 3:		// duplicate
				if(confirm("<?php echo $_lang['confirm_duplicate_record'] ?>")==true) {
					window.location.href='index.php?a=111&id='+id;
				}
				break;			
			case 4:		// delete
				if(confirm("<?php echo $_lang['confirm_delete_module']; ?>")==true) {			
					window.location.href='index.php?a=110&id='+id;
				}
				break;			
		}
	}

	document.addEventListener("onclick",function(){
		contextm.setVisible(false);
	})	
</script> 
<form name="resource" method="post">
<input type="hidden" name="id" value="<?php echo $id; ?>" />
<input type="hidden" name="listmode" value="<?php echo $listmode; ?>" />
<input type="hidden" name="op" value="" />
<div class="sectionHeader"><img src='media/images/misc/dot.gif' alt="." />&nbsp;<?php echo $_lang['module_management']; ?></div><div class="sectionBody">
	<!-- load modules -->
	<p><img src='media/images/icons/modules.gif' alt="." width="32" height="32" align="left" hspace="10" /><?php echo $_lang['module_management_msg']; ?></p>		
	<div class="searchbar">
		<table border="0" style="width:100%">
			<tr>
			<td><a class="searchtoolbarbtn" href="index.php?a=107"><img src="media/images/icons/save.gif"  align="absmiddle" /> <?php echo $_lang['new_module']; ?></a></td>
			<td nowrap="nowrap">
				<table border="0" style="float:right"><tr><td>Search </td><td><input class="searchtext" name="search" type="text" size="15" value="<?php echo $query; ?>" /></td>
				<td><a href="javascript:;" class="searchbutton" title="<?php echo $_lang["search"];?>" onclick="searchResource();return false;">Go</a></td>
				<td><a href="javascript:;" class="searchbutton" title="<?php echo $_lang["reset"];?>" onclick="resetSearch();return false;"><img src="media/images/icons/refresh.gif" width="16" height="16"/></a></td>
				<td><a href="javascript:;" class="searchbutton" title="<?php echo $_lang["list_mode"];?>" onclick="changeListMode();return false;"><img src="media/images/icons/table.gif" width="16" height="16"/></a></td>
				</tr>
				</table>
			</td>
			</tr>
		</table>
	</div>	
	<br />
	<div>
	<?php

	$sql = "SELECT id,name,description,IF(locked,'Yes','-') as 'locked',IF(disabled,'".$_lang['yes']."','-') as 'disabled',IF(icon<>'',icon,'media/images/icons/module.gif') as'icon' " .
			"FROM ".$modx->getFullTableName("site_modules")." ".
			(!empty($sqlQuery) ? " WHERE (name LIKE '%$sqlQuery%') OR (description LIKE '%$sqlQuery%')":"")." ".			
			"ORDER BY name"; 
	$ds = mysql_query($sql); 
	include_once $base_path."manager/includes/controls/datagrid.class.php";
	$grd = new DataGrid('',$ds,$number_of_results); // set page size to 0 t show all items	
	$grd->noRecordMsg = $_lang["no_records_found"];
	$grd->cssClass="grid";
	$grd->columnHeaderClass="gridHeader";
	$grd->itemClass="gridItem"; 
	$grd->altItemClass="gridAltItem";
	$grd->fields="icon,name,description,locked,disabled"; 
	$grd->columns=$_lang["icon"]." ,".$_lang["name"]." ,".$_lang["description"]." ,".$_lang["locked"]." ,".$_lang["disabled"];					
	$grd->colWidths="34,,,60,60";					
	$grd->colAligns="center,,,center,center";
	$grd->colTypes="template:<a class='gridRowIcon' href='#' onclick='return showContentMenu([+id+],event);' title='".$_lang["click_to_context"]."'><img src='[+value+]' width='32' height='32' /></a>||template:<a href='index.php?a=108&id=[+id+]' title='".$_lang["module_edit_click_title"]."'>[+value+]</a>";
	if($listmode=='1') $grd->pageSize=0;
	if($_REQUEST['op']=='reset') $grd->pageNumber = 1;
	// render grid
	echo $grd->render();						
	?>
	</div>
</div>
</form>