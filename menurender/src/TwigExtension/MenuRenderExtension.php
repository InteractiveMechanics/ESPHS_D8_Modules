<?php
 
namespace Drupal\menurender\TwigExtension;

class MenuRenderExtension extends \Twig_Extension {    
  
  public function getFunctions() {
	return array(
    	new \Twig_SimpleFunction('render_menu', array($this, 'render_menu'), array('is_safe' => array('html'))),
    );
  }
  
  
  public function getName() {
    return 'menurender.twig_extension';
  }

  public function render_menu($menu_name) {
    $menu_tree = \Drupal::menuTree();

    // Build the typical default set of menu tree parameters.
    $parameters = $menu_tree->getCurrentRouteMenuTreeParameters($menu_name);

    // Load the tree based on this set of parameters.
    $tree = $menu_tree->load($menu_name, $parameters);

    // Transform the tree using the manipulators you want.
    $manipulators = array(
      // Only show links that are accessible for the current user.
      array('callable' => 'menu.default_tree_manipulators:checkAccess'),
      // Use the default sorting of menu links.
      array('callable' => 'menu.default_tree_manipulators:generateIndexAndSort'),
    );
    $tree = $menu_tree->transform($tree, $manipulators);

    // Finally, build a renderable array from the transformed tree.
    $menu = $menu_tree->build($tree);

    return  array('#markup' => drupal_render($menu));
  }

}