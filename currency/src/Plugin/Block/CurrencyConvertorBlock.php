<?php

namespace Drupal\currency\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * @Block(
 *  id = "currency_converter_block",
 *  admin_label = @Translation("Currency Converter Block"),
 * )
 */
class CurrencyConvertorBlock extends BlockBase {

  /**
   * This function will help to render the form into the block. 
   * @return array
   */
  public function build() {
    //This will load the form from Drupal\currency\Form\FrontPanel.
    $build['form'] = \Drupal::formBuilder()->getForm('Drupal\currency\Form\FrontPanel');
    //This will attach the library file of css and js into the block.
    $build['#attached']['library'][] = 'currency/currency-check';
    return $build;
  }

}
