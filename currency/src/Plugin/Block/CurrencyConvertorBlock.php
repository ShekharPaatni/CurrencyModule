<?php

namespace Drupal\currency\Plugin\Block;
use Drupal\Core\Block\BlockBase;

/**
 * 
 *
 * @Block(
 *  id = "currency_converter_block",
 *  admin_label = @Translation("Currency Converter Block"),
 * )
 */

class CurrencyConvertorBlock extends BlockBase{
  
  public function build() {
    $build['form']= \Drupal::formBuilder()->getForm('Drupal\currency\Form\FrontPanel');
    $build['#attached']['library'][]='currency/currency-check';
    return $build;
  }
}