<?php
require_once(__DIR__ . '/../web.includes.php');

$ibe = new router();
$ibe->init();


$sender = [
    'info@airwin.hu',
    'zsolt@wagnr.hu',
    'info@buchler-wagner.com',
];


include_once('header.php');
?>
<div class="container-fluid">
	<div class="row">
		<div class="col-sm-6">
			<form role="form" class="form-horizontal" id="frm" method="post" action=".">
                <div class="form-group">
                    <div class="col-sm-6">
                        <select class="form-control" id="ev_closed" name="data[ev_closed]" >
                            <option value="0">-</option>
                            <option value="no"<?php if ($data['ev_closed']=='no') { echo ' selected="selected"'; } ?>>not closed</option>
                            <option value="yes"<?php if ($data['ev_closed']=='yes') { echo ' selected="selected"'; } ?>>closed</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-6">
                        <input type="text" class="form-control" placeholder="ev_instructor" value="<?php echo $data['ev_instructor']?>" id="ev_instructor" name="data[ev_instructor]" />
                    </div>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" placeholder="ev_student" value="<?php echo $data['ev_student']?>" id="ev_student" name="data[ev_student]" />
                    </div>
                </div>
			</form>
		</div>
		<div class="col-sm-6">
		</div>
	</div>
</div>


<?php include_once('footer.php'); ?>
