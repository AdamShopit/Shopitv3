<div id="content">
	<div class="table report">
		<h2>Sales Reports</h2>
		<iframe name="_report" id="reportFrame" src="<?=site_url('reports/welcome');?>"></iframe>
	</div>
</div>

<div id="sidebar">
	<h3>Reports</h3>
	<ul>
		<li><strong>Sales</strong></li>
		<li><a href="<?=site_url('reports/salessummary');?>" target="_report">Sales summary</a></li>
		<li><a href="<?=site_url('reports/weeksales');?>" target="_report">This week's sales</a></li>
		<li><a href="<?=site_url('reports/weeklysales');?>" target="_report">Weekly sales</a></li>
		<li><a href="<?=site_url('reports/monthlysales');?>" target="_report">Monthly sales</a></li>
		<li><a href="<?=site_url('reports/quarterlysales');?>" target="_report">Quarterly sales (Financial year)</a></li>
		<li><a href="<?=site_url('reports/yearlysales');?>" target="_report">Yearly sales</a></li>
		<li><strong>Top</strong></li>
		<li><a href="<?=site_url('reports/mostsold');?>" target="_report">Most sold items</a></li>
		<li><a href="<?=site_url('reports/mostviewed');?>" target="_report">Most viewed items</a></li>
		<li><a href="<?=site_url('reports/popularsearches');?>" target="_report">Popular searches</a></li>
		<li><a href="<?=site_url('reports/topcustomers');?>" target="_report">Top customers</a></li>
	</ul>
</div>