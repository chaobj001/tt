<?php /* Smarty version 2.6.26, created on 2010-03-09 19:37:52
         compiled from D:%5Cwww%5Csk80%5Ctemplates/default/post.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'D:\\www\\sk80\\templates/default/post.html', 29, false),)), $this); ?>
<?php echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="zh" lang="zh" dir="ltr"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /><meta name="generator" content="4kychao\'s simpleblog" /><meta name="keywords" content="'; ?><?php echo $this->_tpl_vars['post']->title; ?><?php echo ':'; ?><?php echo $this->_tpl_vars['post']->category; ?><?php echo ','; ?><?php $_from = $this->_tpl_vars['post']->tags; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['tag_keyword'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['tag_keyword']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['tag']):
        $this->_foreach['tag_keyword']['iteration']++;
?><?php echo ''; ?><?php echo $this->_tpl_vars['tag']; ?><?php echo ' '; ?><?php endforeach; endif; unset($_from); ?><?php echo '-www.sk80.com" /><meta name="description" content="'; ?><?php echo $this->_tpl_vars['post']->title; ?><?php echo ','; ?><?php echo $this->_tpl_vars['next']->title; ?><?php echo ','; ?><?php echo $this->_tpl_vars['previous']->title; ?><?php echo '-www.sk80.com" /><meta name="author" content="4kychao" /><link rel="stylesheet" type="text/css" href="'; ?><?php echo $this->_tpl_vars['tpl_front']; ?><?php echo 'style.css" /><title>'; ?><?php echo $this->_tpl_vars['post']->title; ?><?php echo ' - sk80.com</title></head><body><div id="wrapper"><div id="header"><div id="header-inner"><h1>4kychao\'s simpleblog</h1><div class="description">如果这不能成为一项脑力活，那么将成为一项体力活</div><div class="menu"><ul><li class="current_page"><a href="'; ?><?php echo $this->_tpl_vars['site_url']; ?><?php echo '">Blog</a></li><li><a href="#">About</a></li></ul></div></div></div><div id="wrapper-inner"><div id="content"><div class="date">'; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['post']->created_date)) ? $this->_run_mod_handler('date_format', true, $_tmp) : smarty_modifier_date_format($_tmp)); ?><?php echo '</div><div id="latest-post" class="post"><h1 class="entry-title">'; ?><?php echo $this->_tpl_vars['post']->title; ?><?php echo '</h1><div class="entry-meta"><div class="entry-date">'; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['post']->created_date)) ? $this->_run_mod_handler('date_format', true, $_tmp, "%H:%M:%S") : smarty_modifier_date_format($_tmp, "%H:%M:%S")); ?><?php echo '</div><div class="categories"><h3>Category</h3><p><a href="'; ?><?php echo $this->_tpl_vars['site_url']; ?><?php echo 'category/'; ?><?php echo $this->_tpl_vars['post']->category_slug; ?><?php echo '/">'; ?><?php echo $this->_tpl_vars['post']->category; ?><?php echo '</a></p></div><!--div class="comments"><a href="#">Comments</a></div--></div><div class="entry-content">'; ?><?php echo $this->_tpl_vars['post']->content; ?><?php echo '</div>'; ?><?php if ($this->_tpl_vars['post']->tags): ?><?php echo '<div class="entry-tags"><strong>Tags:</strong>'; ?><?php $_from = $this->_tpl_vars['post']->tags; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['tag'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['tag']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['tag']):
        $this->_foreach['tag']['iteration']++;
?><?php echo '<a href="'; ?><?php echo $this->_tpl_vars['site_url']; ?><?php echo 'tag/'; ?><?php echo $this->_tpl_vars['tag']; ?><?php echo '/">'; ?><?php echo $this->_tpl_vars['tag']; ?><?php echo '</a>'; ?><?php if (! ($this->_foreach['tag']['iteration'] == $this->_foreach['tag']['total'])): ?><?php echo ','; ?><?php endif; ?><?php echo ''; ?><?php endforeach; endif; unset($_from); ?><?php echo '</div>'; ?><?php endif; ?><?php echo '</div><ul id="next-prev">'; ?><?php if ($this->_tpl_vars['next']): ?><?php echo '<li><a href="'; ?><?php echo $this->_tpl_vars['site_url']; ?><?php echo 'post/'; ?><?php echo $this->_tpl_vars['next']->id; ?><?php echo '.html">较新一篇: '; ?><?php echo $this->_tpl_vars['next']->title; ?><?php echo '</a></li>'; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['previous']): ?><?php echo '<li><a href="'; ?><?php echo $this->_tpl_vars['site_url']; ?><?php echo 'post/'; ?><?php echo $this->_tpl_vars['previous']->id; ?><?php echo '.html">较旧一篇: '; ?><?php echo $this->_tpl_vars['previous']->title; ?><?php echo '</a></li>'; ?><?php endif; ?><?php echo '</ul><fieldset id="new-post"><legend>随机文章</legend><ul>'; ?><?php $_from = $this->_tpl_vars['recent_post']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['recent_post'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['recent_post']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['val']):
        $this->_foreach['recent_post']['iteration']++;
?><?php echo '<li>'; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['val']->created_date)) ? $this->_run_mod_handler('date_format', true, $_tmp) : smarty_modifier_date_format($_tmp)); ?><?php echo '<a href="'; ?><?php echo $this->_tpl_vars['site_url']; ?><?php echo 'post/'; ?><?php echo $this->_tpl_vars['val']->id; ?><?php echo '.html">'; ?><?php echo $this->_tpl_vars['val']->title; ?><?php echo '</a></li>'; ?><?php endforeach; endif; unset($_from); ?><?php echo '</ul></fieldset></div><div id="sidebar"><ul><li><form id="searchform" action=""><h2>搜索</h2><input type="text" /><input type="submit" value="GO" /></form></li>'; ?><?php if ($this->_tpl_vars['categories']): ?><?php echo '<li><h2>分类</h2><ul>'; ?><?php $_from = $this->_tpl_vars['categories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['categories'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['categories']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['val']):
        $this->_foreach['categories']['iteration']++;
?><?php echo '<li><a href="'; ?><?php echo $this->_tpl_vars['site_url']; ?><?php echo 'category/'; ?><?php echo $this->_tpl_vars['val']->slug; ?><?php echo '/">'; ?><?php echo $this->_tpl_vars['val']->name; ?><?php echo '</a> <small>('; ?><?php echo $this->_tpl_vars['val']->count; ?><?php echo ')</small></li>'; ?><?php endforeach; endif; unset($_from); ?><?php echo '</ul></li>'; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['tag_list']): ?><?php echo '<li id="tags"><h2>标签</h2><ul>'; ?><?php $_from = $this->_tpl_vars['tag_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['tag_list'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['tag_list']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['val']):
        $this->_foreach['tag_list']['iteration']++;
?><?php echo '<li><a href="'; ?><?php echo $this->_tpl_vars['site_url']; ?><?php echo 'tag/'; ?><?php echo $this->_tpl_vars['val']->name; ?><?php echo '/">'; ?><?php echo $this->_tpl_vars['val']->name; ?><?php echo '</a></li>'; ?><?php endforeach; endif; unset($_from); ?><?php echo '</ul></li>'; ?><?php endif; ?><?php echo '</ul></div>'; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "default/footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>