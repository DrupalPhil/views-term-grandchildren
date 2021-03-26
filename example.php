<?php

/**
 * Implements hook_views_pre_build().
 *
 * Unfortunately, this only works for taxonomy vocabs that are
 * ONLY 3 levels deep and we absolutely only need the grandchildren
 * */
function MYMODULE_views_pre_build(\Drupal\views\ViewExecutable $view) {
  if ($view->id() == 'job_families_profiles' && $view->getDisplay()->display['id'] == 'block_1') {
    $exposedInput = $view->getExposedInput();
    if($tid = $exposedInput['tid']) {

      // Check depth of exposed input
      $parents = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadAllParents($tid);
      $depth = count($parents);

      $args = [];
      if($depth === 3) {
        // If depth is 3, just return the value
        // We're at the deepest child
        $args = [$tid];
      } else {
        // If the grandparent was chosen, get the grandchildren
        // If the parent was chosen, get the child
        $vid = 'job_family_groups';
        switch ($depth) {
          case 1: // Grandparent
            $depth = 2;
            break;
          case 2: // Parent
            $depth = 1;
            break;
        }

        // Gather term children
        $child_tids = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid, $tid, $depth, FALSE);

        // Turn children into arguments
        // <insert punny joke here about children and arguments>
        foreach ($child_tids as $child_tid) {
          $args[] = $child_tid->tid;
        }
        $args = [implode('+', $args)];
      }


      // Set exposed input (for tid, anyway) to null
      // We don't want to compound the exposed filter with the contextual args
      $view->setExposedInput(['tid' => NULL]);

      // Manually define the children (set contextual arg)
      $view->setArguments($args);
    }

  }
}
