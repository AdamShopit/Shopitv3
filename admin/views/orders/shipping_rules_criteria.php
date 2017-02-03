<option value="less than" <?=is_selected('less than',set_value('operation',$shipping->operation));?>>less than</option>
<option value="more than"<?=is_selected('more than',set_value('operation',$shipping->operation));?>>more than</option>
<option value="equal to" <?=is_selected('equal to',set_value('operation',$shipping->operation));?>>equal to</option>
<option value="between" <?=is_selected('between',set_value('operation',$shipping->operation));?>>between</option>