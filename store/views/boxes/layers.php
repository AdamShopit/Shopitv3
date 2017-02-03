<ul>
	{layers_selected}
	<li><strong>{selected_group}{if {selected_group} != 'Active Filters'}:{/if}</strong> {selected_layer} {selected_url}</li>	
	{/layers_selected}
</ul>

{layers}
	
	{if {group_empty} == 'false'}
	<fieldset>
		<legend>{group_label}</legend>
		<ul>
		{layer}
			{field_html}
		{/layer}
		</ul>
	</fieldset>
	{/if}
	
{/layers}