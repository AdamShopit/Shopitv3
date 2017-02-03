<!-- Hidden links to slides -->
<ul style="display:none;">
	<li><a class="shopit-new-features-popup" href="#shopit-new-features-info-page1" rel="gallery"></a></li>
	<?php
	// Add more links to slides below. Each link should have a class "shopit-new-features-popup" and
	// rel of "gallery". The href should link to a div id below.
	?>
	<li><a class="shopit-new-features-popup" href="#shopit-new-features-info-page2" rel="gallery"></a></li>
	<li><a class="shopit-new-features-popup" href="#shopit-new-features-info-page3" rel="gallery"></a></li>
</ul>

<?php
// Content slides
// Each slide <div> should have a unique id "shopit-new-features-info-page{n}" and
// the class "shopit-new-features-info"
?>
<div id="shopit-new-features-info-page1" class="shopit-new-features-info">
	
	<img src="<?=template_directory('views/misc/images/admin-bar.gif');?>" alt="" title="" style="float: right;" />
	
	<div>
		<h1>Introducing the new Admin Bar</h1>
	
		<p>We're excited to introduce one of our most exciting new features to Shopit 3 &mdash; the Admin Bar.</p>
	
		<p>This new feature enables you to browse your store front and make immediate changes to site content by automatically switching you to the appropriate area of the admin. And depending on the page you're currently viewing, you'll have options available that are related to that page type. </p>
		
		<p>Once you've saved your changes, you'll be redirected back to the store page to continue browsing &mdash; Happy editing!</p>
		
	</div>

</div>

<div id="shopit-new-features-info-page2" class="shopit-new-features-info">
	
	<img src="<?=template_directory('views/misc/images/snippets.gif');?>" alt="" title="" style="float: right;" />
	
	<div>
		<h1>Highlighted snippets</h1>
		
		<p>We love the snippets feature in Shopit, it's one we use the most often to make areas of a website editable.</p>
		
		<p>Together with the new <strong>Admin Bar</strong>, we've made snippets easier to work with by simply highlighting them within the store front and providing a direct link to the edit snippet page in the admin.</p>
		
		<p>So, making that change to that block on the homepage or links in the footer is as easy as a click &mdash; no more searching through the admin to find it!</p>
	</div>
	
</div>

<div id="shopit-new-features-info-page3" class="shopit-new-features-info">
	
	<img src="<?=template_directory('views/misc/images/shortcuts.gif');?>" alt="" title="" style="float: right;" />
	
	<div>
		<h1>Shortcuts</h1>
		
		<p>You can now add your own custom shortcuts to the orders and inventory menu dropdowns in the admin, enabling you to "bookmark" those screens you use frequently.</p>
		
		<p>To get started, browse to the page in the admin, filter the results, and when your ready click the new red <strong>add</strong> button which appears in the menu dropdowns to give it a label. </p>
		
		<p>Your bookmark will now appear as a menu item  &mdash; it's pretty smart!</p>
	</div>
	
</div>