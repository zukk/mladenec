<?php /* Smarty version 2.6.18, created on 2015-12-28 11:46:18
         compiled from database-step.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'ox_wizard_steps', 'database-step.html', 3, false),array('function', 't', 'database-step.html', 7, false),array('modifier', 'cat', 'database-step.html', 16, false),)), $this); ?>
<div class="install-wizard">
  <div class="dbStep">
    <?php echo $this->_plugins['function']['ox_wizard_steps'][0][0]->wizardSteps(array('steps' => $this->_tpl_vars['oWizard']->getSteps(),'current' => $this->_tpl_vars['oWizard']->getCurrentStep()), $this);?>


    <div class="content">
      <?php if ($this->_tpl_vars['isUpgrade']): ?>
      <h2><?php echo OA_Admin_Template::_function_t(array('str' => 'DbUpgradeTitle'), $this);?>
</h2>
      <p><?php echo OA_Admin_Template::_function_t(array('str' => 'DbUpgradeIntro'), $this);?>
</p>
      <?php else: ?>
      <h2><?php echo OA_Admin_Template::_function_t(array('str' => 'DbSetupTitle'), $this);?>
</h2>
      <p><?php echo OA_Admin_Template::_function_t(array('str' => 'DbSetupIntro'), $this);?>
</p>
      <?php endif; ?>

      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'messages.html', 'smarty_include_vars' => array('aMessages' => $this->_tpl_vars['aMessages'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ((is_array($_tmp=$this->_tpl_vars['oaTemplateDir'])) ? $this->_run_mod_handler('cat', true, $_tmp, 'form/form.html') : smarty_modifier_cat($_tmp, 'form/form.html')), 'smarty_include_vars' => array('form' => $this->_tpl_vars['form'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </div>
  </div>
</div>


<script type="text/javascript">
<!--
<?php echo '
  $(document).ready(function() {
    $(".dbStep").dbStep({
    '; ?>

        'formFrozen' : <?php if ($this->_tpl_vars['form']['frozen'] || $this->_tpl_vars['isUpgrade']): ?>true<?php else: ?>false<?php endif; ?>,
        'message' : '<?php echo $this->_tpl_vars['loaderMessage']; ?>
'
    <?php echo '
    });
  });
'; ?>

-->
</script>