<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\currency\Controller;

use \Drupal\Core\Database\Connection;
use Drupal\Core\Controller\ControllerBase;
use \Symfony\Component\HttpFoundation\JsonResponse;

class CurrencyControllerApi extends ControllerBase {

  protected $database;
/**
 * 
 * @param Connection $database
 */
  public function __construct(Connection $database) {
    $this->database = $database;
  }
/**
 * 
 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
 * @return \static
 */
  public static function create(\Symfony\Component\DependencyInjection\ContainerInterface $container) {

    return new static(
        $container->get('database')
    );
  }

  /**
   * This function will  help the outside user to hit the url and get
   *  the json data.
   * @param type $from
   * @param type $to
   * @param type $amount
   * @return JsonResponse
   */
  public function apiHit($from, $to, $amount) {

    $resi = $this->database->select('currency_offlne_data', 'c')
            ->fields('c', ['price'])
            ->condition('destination_currency', $to, '=')
            ->condition('date', Date('Y-m-d'), '=')
            ->execute()->fetchAll();
    $result = json_decode(json_encode($resi), TRUE);

    $res = $this->database->select('currency_offlne_data', 'c')
            ->fields('c', ['price'])
            ->condition('destination_currency', $from, '=')
            ->condition('date', Date('Y-m-d'), '=')
            ->execute()->fetchAll();

    $resultsecond = json_decode(json_encode($res), TRUE);
    return new JsonResponse(['Data' => ((1 / $resultsecond[0]['price']) * $result[0]['price']) * $amount]);
  }
}
