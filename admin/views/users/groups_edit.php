<div id="content">

	<div class="table">
	
		<h2>Manage User Group</h2>
	
		<?php if (validation_errors()) { ?>
		<p class="error_notice">Sorry, we found some errors with your information. Please check below.</p>
		<?php } ?>
	
		<div class="table-row">
			<h3>Group Details</h3>
		</div>
				
		<div class="table-row">
			<label>Title: <span class="red">*</span></label>
			<input name="group_title" value="<?=set_value('group_title', $group->group_title);?>" class="textbox required" size="75" maxlength="100" autocomplete="off" />
			<?=form_error('group_title');?>
		</div>

		<div class="table-row">
			<label>Description:</label>
			<textarea name="group_description" id="group_description" class="textbox"><?=set_value('group_description', hidep($group->group_description));?></textarea>
			<?=form_error('product_description');?>
		</div>

		<div class="table-row">
			<h3>Permissions</h3>
		</div>
		
		<div class="table-row">

			<!-- !Dashboard -->
			<fieldset class="fullwidth multiple permissions">
				<legend>Dashboard</legend>
				<label><input type="checkbox" name="can_access_dashwidgets_sales" value="1" <?=is_checked(1, set_value('can_access_dashwidgets_sales', $group->can_access_dashwidgets_sales));?> /> Sales Widgets</label>
				<label><input type="checkbox" name="can_access_dashwidgets_orders" value="1" <?=is_checked(1, set_value('can_access_dashwidgets_orders', $group->can_access_dashwidgets_orders));?> /> Orders Widgets</label>
				<label><input type="checkbox" name="can_access_dashwidgets_inventory" value="1" <?=is_checked(1, set_value('can_access_dashwidgets_inventory', $group->can_access_dashwidgets_inventory));?> /> Inventory Widgets</label>
				<label><input type="checkbox" name="can_access_dashwidgets_stats" value="1" <?=is_checked(1, set_value('can_access_dashwidgets_stats', $group->can_access_dashwidgets_stats));?> /> Stats Widgets</label>
				<label><input type="checkbox" name="can_access_dashwidgets_realtime" value="1" <?=is_checked(1, set_value('can_access_dashwidgets_realtime', $group->can_access_dashwidgets_realtime));?> /> Realtime Widgets</label>
			</fieldset>

			<!-- !Misc Sections/Options -->
			<fieldset class="fullwidth multiple permissions">
				<legend>Sections</legend>
				<label><input type="checkbox" name="can_access_reports" value="1" <?=is_checked(1, set_value('can_access_reports', $group->can_access_reports));?> /> Reports</label>
				<label><input type="checkbox" name="can_access_pages" value="1" <?=is_checked(1, set_value('can_access_pages', $group->can_access_pages));?> /> Pages</label>
				<label><input type="checkbox" name="can_access_snippets" value="1" <?=is_checked(1, set_value('can_access_snippets', $group->can_access_snippets));?> /> Snippets</label>
				<label><input type="checkbox" name="can_supersearch" value="1" <?=is_checked(1, set_value('can_supersearch', $group->can_supersearch));?> /> Super Search</label>
			</fieldset>

			<!-- !Orders -->
			<fieldset class="fullwidth multiple permissions">
				<legend>Orders</legend>
				<label><input type="checkbox" name="can_access_order_listview" value="1" <?=is_checked(1, set_value('can_access_order_listview', $group->can_access_order_listview));?> /> List/View Order</label>
				<label><input type="checkbox" name="can_access_order_builder" value="1" <?=is_checked(1, set_value('can_access_order_builder', $group->can_access_order_builder));?> rel="can_access_order_listview" /> Order Build/Edit</label>
				<label><input type="checkbox" name="can_access_shipping" value="1" <?=is_checked(1, set_value('can_access_shipping', $group->can_access_shipping));?> /> Shipping Rules</label>
				<label><input type="checkbox" name="can_access_order_custom_fields" value="1" <?=is_checked(1, set_value('can_access_order_custom_fields', $group->can_access_order_custom_fields));?> /> Custom Fields</label>
				<label><input type="checkbox" name="can_access_order_statuses" value="1" <?=is_checked(1, set_value('can_access_order_statuses', $group->can_access_order_statuses));?> /> Order Statuses</label>
				<label><input type="checkbox" name="can_access_order_notifications" value="1" <?=is_checked(1, set_value('can_access_order_notifications', $group->can_access_order_notifications));?> /> Order Notifications</label>
				<label><input type="checkbox" name="can_access_order_exports" value="1" <?=is_checked(1, set_value('can_access_order_exports', $group->can_access_order_exports));?> rel="can_access_order_listview" /> Data Exports</label>
			</fieldset>

			<!-- !Inventory -->
			<fieldset class="fullwidth multiple permissions">
				<legend>Inventory</legend>
				<label><input type="checkbox" name="can_access_inventory_list" value="1" <?=is_checked(1, set_value('can_access_inventory_list', $group->can_access_inventory_list));?> /> List</label>
				<label><input type="checkbox" name="can_access_inventory_addedit" value="1" <?=is_checked(1, set_value('can_access_inventory_addedit', $group->can_access_inventory_addedit));?> rel="can_access_inventory_list" /> Add/Edit Items</label>
				<label><input type="checkbox" name="can_access_collections" value="1" <?=is_checked(1, set_value('can_access_collections', $group->can_access_collections));?> rel="can_access_inventory_list" /> Collections</label>
				<label><input type="checkbox" name="can_access_attribute_sets" value="1" <?=is_checked(1, set_value('can_access_attribute_sets', $group->can_access_attribute_sets));?> /> Attribute Sets</label>
				<label><input type="checkbox" name="can_access_product_option_sets" value="1" <?=is_checked(1, set_value('can_access_product_option_sets', $group->can_access_product_option_sets));?> /> Product Option Sets</label>
				<label><input type="checkbox" name="can_access_inventory_custom_fields" value="1" <?=is_checked(1, set_value('can_access_inventory_custom_fields', $group->can_access_inventory_custom_fields));?> /> Custom Fields</label>
				<label><input type="checkbox" name="can_access_inventory_exports" value="1" <?=is_checked(1, set_value('can_access_inventory_exports', $group->can_access_inventory_exports));?> rel="can_access_inventory_list" /> Data Exports</label>
				<label><input type="checkbox" name="can_access_inventory_reports" value="1" <?=is_checked(1, set_value('can_access_inventory_reports', $group->can_access_inventory_reports));?> rel="can_access_inventory_list" /> Product Reports</label>
			</fieldset>

			<!-- !Categories -->
			<fieldset class="fullwidth multiple permissions">
				<legend>Categories</legend>
				<label><input type="checkbox" name="can_access_category_list" value="1" <?=is_checked(1, set_value('can_access_category_list', $group->can_access_category_list));?> /> List</label>
				<label><input type="checkbox" name="can_access_category_addedit" value="1" <?=is_checked(1, set_value('can_access_category_addedit', $group->can_access_category_addedit));?> rel="can_access_category_list" /> Add/Edit Category</label>
				<label><input type="checkbox" name="can_access_category_exports" value="1" <?=is_checked(1, set_value('can_access_category_exports', $group->can_access_category_exports));?> rel="can_access_category_list" /> Data Exports</label>
			</fieldset>

			<!-- !Customers -->
			<fieldset class="fullwidth multiple permissions">
				<legend>Customers</legend>
				<label><input type="checkbox" name="can_access_customer_list" value="1" <?=is_checked(1, set_value('can_access_customer_list', $group->can_access_customer_list));?> /> List</label>
				<?php if (library_exists('myaccount')) { ?>
				<label><input type="checkbox" name="can_module_myaccount" value="1" <?=is_checked(1, set_value('can_module_myaccount', $group->can_module_myaccount));?> /> Customer Accounts</label>
				<?php } ?>
				<label><input type="checkbox" name="can_access_customer_exports" value="1" <?=is_checked(1, set_value('can_access_customer_exports', $group->can_access_customer_exports));?> rel="can_access_customer_list" /> Data Exports</label>
			</fieldset>

			<!-- !Options -->
			<fieldset class="fullwidth multiple permissions">
				<legend>Options</legend>
				<label><input type="checkbox" name="can_access_options_summary" value="1" <?=is_checked(1, set_value('can_access_options_summary', $group->can_access_options_summary));?> /> Services &amp; Modules</label>
				<label><input type="checkbox" name="can_access_options_services" value="1" <?=is_checked(1, set_value('can_access_options_services', $group->can_access_options_services));?> /> Services</label>
				<label><input type="checkbox" name="can_access_options_tools" value="1" <?=is_checked(1, set_value('can_access_options_tools', $group->can_access_options_tools));?> /> Tools</label>
				<label><input type="checkbox" name="can_access_options_redirection" value="1" <?=is_checked(1, set_value('can_access_options_redirection', $group->can_access_options_redirection));?> /> 301 Redirection</label>
			</fieldset>

			<!-- !Modules -->
			<fieldset class="fullwidth multiple permissions">
				<legend>Installed Modules</legend>
				<?php if (library_exists('filters')) { ?>
				<label><input type="checkbox" name="can_module_filters" value="1" <?=is_checked(1, set_value('can_module_filters', $group->can_module_filters));?> rel="can_access_category_list" /> Filters</label>
				<?php } ?>
				<label><input type="checkbox" name="can_module_coupons" value="1" <?=is_checked(1, set_value('can_module_coupons', $group->can_module_coupons));?> /> Coupons</label>
				<?php if (library_exists('stocklocations')) { ?>
				<label><input type="checkbox" name="can_module_stocklocations" value="1" <?=is_checked(1, set_value('can_module_stocklocations', $group->can_module_stocklocations));?> /> Stock Locations</label>
				<?php } ?>
			</fieldset>
			
			<!-- !Admins Only -->
			<fieldset class="fullwidth multiple permissions">
				<legend><span class="redtext">Admins Only</span></legend>
				<label><input type="checkbox" name="can_access_admin_config" value="1" <?=is_checked(1, set_value('can_access_admin_config', $group->can_access_admin_config));?> /> Site Settings</label>
				<label><input type="checkbox" name="can_access_admin_backup" value="1" <?=is_checked(1, set_value('can_access_admin_backup', $group->can_access_admin_backup));?> /> Database Backup</label>
				<label><input type="checkbox" name="can_access_admin_tools" value="1" <?=is_checked(1, set_value('can_access_admin_tools', $group->can_access_admin_tools));?> /> Developer Tools</label>
				<label class="redtext"><input type="checkbox" name="can_manage_users" value="1" <?=is_checked(1, set_value('can_manage_users', $group->can_manage_users));?> /> Manage Users &amp; Groups</label>
			</fieldset>

			<input type="hidden" name="group_id" value="<?=$this->uri->segment(4);?>" autocomplete="off" />

		</div>
		
	</div>

</div>

<div id="sidebar">
	<h3>Group Permissions</h3>
	<p></p>
</div>