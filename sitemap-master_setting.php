<?php
/*
Plugin Name: sitemap
Version: 1.0
Plugin URL: http://www.qiyuuu.com/for-emlog/emlog-plugin-sitemap
Description: 生成sitemap，供搜索引擎抓取
Author: 奇遇
Author Email: qiyuuu@gmail.com
Author URL: http://www.qiyuuu.com
*/
!defined('EMLOG_ROOT') && exit('access deined!');
function plugin_setting_view()
{
	extract(sitemap_config());
	$ex1 = $ex2 = '';
	if($sitemap_show_footer) $ex1 = 'checked="checked" ';
	if($sitemap_comment_time) $ex2 = 'checked="checked" ';
?>

<div class="heading-bg  card-views">
<ul class="breadcrumbs">
<li><a href="./"><i class="fa fa-home"></i> 首页</a></li>
<li class="active">SITEMAP</li>
</ul>
</div>

<?php if(isset($_GET['setting'])):?>
<div class="actived alert alert-success alert-dismissable">
<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
插件设置完成
</div>
<?php endif;?>
<?php if(isset($_GET['error'])):?>
<div class="actived alert alert-danger alert-dismissable">
<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
插件设置失败
</div>
<?php endif;?>
<div class="row">
<div class="col-lg-12">
<div class="panel panel-primary card-view">
<div class="panel-heading">
<div class="pull-left">
<h6 class="panel-title txt-light">温馨提示</h6>
</div>
<div class="clearfix"></div>
</div>
<div class="panel-wrapper collapse in">
<div class="panel-body">
<p>注：对以下选项不了解者请保持默认，参考资料<a href="http://www.sitemaps.org/zh_CN/protocol.php">sitemaps.org</a></p>
</div>
</div>
</div>

<form action="plugin.php?plugin=sitemap-master&action=setting" method="post">
<div class="row">
<div class="col-lg-12">
<div class="panel panel-default card-view">
<div class="tab-content">
<div class="form-group">
    <label>SITEMAP文件名</label>
     <input size="12" name="sitemap_name" type="text" class="form-control"  value="<?php echo $sitemap_name; ?>" />
</div>
<div class="checkbox checkbox-success">
<label> 在网站底部显示 </label>		
<input size="12" name="sitemap_show_footer" type="checkbox" value="1" <?php echo $ex1;?>/> 
</div>
<div class="checkbox checkbox-success">	
<label>  最新评论时间作为最后更新时间 </label>	<input size="12" name="sitemap_comment_time" type="checkbox" value="1" <?php echo $ex2;?>/>
</div>
</div>
<div class="table-wrap" style="padding-top:10px">
<div class="table-responsive">
<table id="adm_link_list"  class="table table-striped table-bordered mb-0">
    <thead>
		<tr>

				<th widtd="14%"></th>
				<th widtd="14%">日志</th>
				<th widtd="14%">页面</th>
				<th widtd="14%">分类</th>
				<th widtd="14%">标签</th>
				<th widtd="14%">归档</th>
				<th widtd="14%">评论</th>
			</tr>
    </thead>
    <tbody>			
			<tr align="center">
				<td align="right">更新周期</td>
			<?php foreach($sitemap_changefreq as $value): ?>
				<td><input size="5" class="form-control em-small" name="sitemap_changefreq[]" type="text" value="<?php echo $value; ?>" /></td>
			<?php endforeach; ?>
			</tr>
			<tr align="center">
				<td align="right">权重</td>
			<?php foreach($sitemap_priority as $value): ?>
				<td><input size="5" class="form-control em-small" name="sitemap_priority[]" type="text" value="<?php echo $value; ?>" /></td>
			<?php endforeach; ?>
			</tr>
		</tbody>
	</table>
	</div>
	<div class="form-group" style="padding-top:10px">
	<input type="submit" value="保 存" class="submit  btn btn-success" /></div>
</div>
</form>


<div class="form-group">
<form action="plugin.php?plugin=sitemap&-mamsteraction=setting" method="post">
	<input type="hidden" name="update" value="1" />
	<input type="submit" value="更新sitemap" class="submit  btn btn-primary" />
</div>
</form>
</div>
<script>
$("#sitemap").addClass('sidebarsubmenu1');
setTimeout(hideActived,2600);
</script>
<?php 
}
function plugin_setting()
{
	extract(sitemap_config());
	if(!isset($_POST['update'])) {
		$changefreq2 = isset($_POST['sitemap_changefreq']) ? $_POST['sitemap_changefreq'] : array();
		$priority2 = isset($_POST['sitemap_priority']) ? $_POST['sitemap_priority'] : array();
		$sitemap_name2 = isset($_POST['sitemap_name']) ? strval($_POST['sitemap_name']) : '';
		foreach($changefreq2 as $key=>$value) {
			if(!in_array($value,array('always','hourly','daily','weekly','monthly','yearly','never'))){
				$sitemap_changefreq[$key] = 'daily';
			} else {
				$sitemap_changefreq[$key] = $value;
			}
		}
		foreach($priority2 as $key=>$value) {
			if(floatval($value) > 1.0 || floatval($value) <= 0){
				$sitemap_priority[$key] = '0.8';
			} else {
				$sitemap_priority[$key] = $value;
			}
		}
		if($sitemap_name2 != '' && $sitemap_name != $sitemap_name2) {
			if(!@rename(EMLOG_ROOT . '/' . $sitemap_name, EMLOG_ROOT . '/' . $sitemap_name2)) {
				emMsg("重命名文件{$sitemap_name}失败,请设置根目录下{$sitemap_name}权限为777（LINUX)/everyone可写（windows）",'./plugin.php?plugin=sitemap-master');
			}
			$sitemap_name = $sitemap_name2;
		}
		$sitemap_show_footer = isset($_POST['sitemap_show_footer']) ? addslashes($_POST['sitemap_show_footer']) : 0;
		$sitemap_comment_time = isset($_POST['sitemap_comment_time']) ? addslashes($_POST['sitemap_comment_time']) : 0;
		if(!@file_put_contents(EMLOG_ROOT . '/content/plugins/sitemap-master/config',serialize(compact('sitemap_name','sitemap_changefreq','sitemap_priority','sitemap_show_footer','sitemap_comment_time')))) {
			emMsg("更新配置失败,请设置文件content/plugins/sitemap-master/config权限为777（LINUX）/everyone可写（windows）",'./plugin.php?plugin=sitemap-master');
		}
	}
	if(!sitemap_update()) {
		emMsg("更新sitemap失败,请设置根目录下{$sitemap_name}权限为777（LINUX）/everyone可写（windows）",'./plugin.php?plugin=sitemap-master');
	}
	return true;
}
