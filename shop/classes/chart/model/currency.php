<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		charts of currency
 * @package 	Shop
 * @author 		A. St.
 * @date 		21.04.2015
 * @use			kohana-charts (https://github.com/butschster/Kohana-Charts)
 *
 **/
 
class Chart_Model_Currency extends Chart {

    protected function _build_data() 
    {
		$orm = ORM::factory('currency')
				->order_by('date')
				->where('code_char', 'IN', DB::expr('("'.implode('","', Site::config('shop')->available_currency).'")'))
// 				->where('code_char', '=', $this->_config['currency'])
				->where('date', '>=', date('Y-m-d H:i:s', time()-365/4*86400));

        $data = array( );
		
		foreach ($orm->find_all( ) AS $item)
		{
			$data[] = array('date' => Date::format($item->date, DATE::FORMAT_RFC3339_S), $item->code_char == 'EUR' ? 'count_eur' : 'count_usd' => $item->rate);
		}
		
		$this->_data = $data;

        unset($data, $orm_data);
    }

    protected function _build_chart() 
    {
        $chart = AmChart::type('serial');
        $chart->dataProvider = $this->_data;
        $chart->categoryField = 'date';
//         $chart->legend = array(
// 			'useGraphSettings' => TRUE,
//         );
        $chart->marginTop = 22;
        $chart->marginLeft = 40;

        $graph = new Chart_Module_Graph;
        $graph->valueField = "summ";
        $graph->type = "column";
        $graph->title = "USD";
        $graph->bullet = "none";
        $graph->balloonText = "USD: [[value]]";
        $graph->valueField = "count_usd";
        $graph->type = "line";
        $graph->lineThickness = 2;
        $graph->fillAlphas = 0.3;
        $chart->addGraph($graph);

        $graph = new Chart_Module_Graph;
        $graph->valueField = "summ";
        $graph->type = "column";
        $graph->title = "EUR";
        $graph->bullet = "none";
        $graph->balloonText = "EUR: [[value]]";
        $graph->valueField = "count_eur";
        $graph->type = "line";
        $graph->lineThickness = 2;
        $graph->fillAlphas = 0.3;
        $chart->addGraph($graph);
        
        
        
        $axis = new Chart_Module_ValueAxis;
        $axis->axisAlpha = 0.5;
        $axis->dashLength = 10;
        $chart->addValueAxis($axis);
/*
        $axis = new Chart_Module_ValueAxis;
        $axis->axisAlpha = 0.5;
        $axis->dashLength = 1;
        $chart->addValueAxis($axis);*/
        
        $catAxis = new Chart_Module_CategoryAxis;
        $catAxis->parseDates = true;
        $chart->addCategoryAxis($catAxis);
        
        $legend = new Chart_Module_Legend;
        $legend->align = 'right';
        $legend->marginLeft = 14;
        $chart->addLegend($legend);

        $cursor = new Chart_Module_Cursor;
        $chart->addChartCursor($cursor);

        $chart->write($this->_id);

        $this->_chart = $chart->render();
    }
}