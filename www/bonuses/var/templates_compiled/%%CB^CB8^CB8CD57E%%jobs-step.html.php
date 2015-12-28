<?php /* Smarty version 2.6.18, created on 2015-12-28 11:51:28
         compiled from jobs-step.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'ox_wizard_steps', 'jobs-step.html', 3, false),array('function', 't', 'jobs-step.html', 7, false),)), $this); ?>
<div class="install-wizard">
  <div class="jobsStep">
    <?php echo $this->_plugins['function']['ox_wizard_steps'][0][0]->wizardSteps(array('steps' => $this->_tpl_vars['oWizard']->getSteps(),'current' => $this->_tpl_vars['oWizard']->getCurrentStep()), $this);?>

  
    <div class="content">
      <?php if ($this->_tpl_vars['isUpgrade']): ?>
        <h2><?php echo OA_Admin_Template::_function_t(array('str' => 'JobsUpgradeTitle'), $this);?>
</h2>
        <p><?php echo OA_Admin_Template::_function_t(array('str' => 'JobsUpgradeIntro'), $this);?>
</p>      
      <?php else: ?>
        <h2><?php echo OA_Admin_Template::_function_t(array('str' => 'JobsInstallTitle'), $this);?>
</h2>
        <p><?php echo OA_Admin_Template::_function_t(array('str' => 'JobsInstallIntro'), $this);?>
</p> 
      <?php endif; ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'messages.html', 'smarty_include_vars' => array('aMessages' => null,'forceRender' => true,'id' => 'errors','class' => 'hide')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
  
      <form id="jobsForm" action="" method="post">
          <div class='err'></div>
          <input type="hidden" name="action" value="pluginTask" >
          <div class="controls">
            <input type="submit" id="continue" value="<?php echo OA_Admin_Template::_function_t(array('str' => 'BtnContinue'), $this);?>
" name="continue"/>
          </div>    
      </form>
    </div>
  </div>
</div>

<script type="text/javascript" src="<?php echo $this->_tpl_vars['assetPath']; ?>
/js/ox.jobs.js"></script>
<script type="text/javascript">
<!--
<?php echo '
  $(document).ready(function() {
    $(".jobsStep").jobsStep({
        '; ?>

            'message' : '<?php if ($this->_tpl_vars['isUpgrade']): ?><?php echo OA_Admin_Template::_function_t(array('str' => 'JobsProgressUpgradeMessage'), $this);?>
<?php else: ?><?php echo OA_Admin_Template::_function_t(array('str' => 'JobsProgressInstallMessage'), $this);?>
<?php endif; ?>',
            'jobs' : <?php echo $this->_tpl_vars['jobs']; ?>

        <?php echo ' 
    });
  });  
'; ?>

-->
</script>