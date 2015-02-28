<?php echo $header; ?>
<?php if ($this->ocw->getVersion() < 1.5) { ?>
	<?php if ($error_warning) { ?>	<div class="warning"><?php echo $error_warning; ?></div><?php } ?>
	<?php if ($success) { ?><div class="success"><?php echo $success; ?></div><?php } ?>
	<div class="box">
		<div class="left"></div>
		<div class="right"></div>
		<div class="heading">
			<h1 style="background-image: url('view/image/product.png');"> <?php echo $settings_heading_title; ?></h1>
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
				<h1><img src="view/image/product.png" alt=""/> <?php echo $settings_heading_title; ?></h1>
<?php } ?>
      <div class="buttons"><a onclick="location = '<?php echo $cancel; ?>'" class="button"><span><?php echo $button_cancel; ?></span></a><a onclick="$('#form').submit();" class="button"><span><?php echo $button_save; ?></span></a></div>
    </div>
	  <div class="content">
      <form action="<?php echo $save; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="form">
          <tr>
            <td><?php echo $entry_language; ?></td>
            <td><select name="language_id">
              <?php foreach ($languages as $language) { ?>
              <?php if ($language['language_id'] == $language_id) { ?>
              <option value="<?php echo $language['language_id']; ?>" selected="selected"><?php echo $language['name']; ?></option>
              <?php } else { ?>
              <option value="<?php echo $language['language_id']; ?>"><?php echo $language['name']; ?></option>
              <?php } ?>
              <?php } ?>
              </select></td>
          </tr>
          <?php if ($hasTaxRateClass) { ?>
          <tr>
            <td><?php echo $entry_tax_rate; ?></td>
            <td><select name="tax_rate_id">
              <option value="0"><?php echo $text_none; ?></option>
              <?php foreach ($tax_rates as $tax_rate) { ?>
              <?php if ($tax_rate['tax_rate_id'] == $tax_rate_id) { ?>
              <option value="<?php echo $tax_rate['tax_rate_id']; ?>" selected="selected"><?php echo $tax_rate['name']; ?></option>
              <?php } else { ?>
              <option value="<?php echo $tax_rate['tax_rate_id']; ?>"><?php echo $tax_rate['name']; ?></option>
              <?php } ?>
              <?php } ?>
              </select></td>
          </tr>
          <?php } ?>
        </table>
      </form>
		</div>
  </div>
</div>
<?php if ($this->ocw->getVersion() < 1.5) { ?></div><?php } ?>
<?php echo $footer; ?>