<?php
//----------------------------------------------------------
// in php
function plot_chart_bar($xlabels,$barData,$yTitle,$title,$filename){
    //phpinfo();
     // content="text/plain; charset=utf-8"
    require_once ('../0020-classes/jpgraph-4.3.4/src/jpgraph.php');
    require_once ('../0020-classes/jpgraph-4.3.4/src/jpgraph_bar.php');
    require_once ('../0020-classes/jpgraph-4.3.4/src/jpgraph_line.php');

    $theme = isset($_GET['theme']) ? $_GET['theme'] : null;

    // Create the graph. These two calls are always required
    $graph = new Graph(600,300);    

    $graph->SetScale("textlin");
    if ($theme) {
        $graph->SetTheme(new $theme());
    }
    $theme_class = new AquaTheme;
    $graph->SetTheme($theme_class);


    $top = 60;
    $bottom = 30;
    $left = 80;
    $right = 30;
    //$graph->Set90AndMargin($left,$right,$top,$bottom);  // rotation

    //$plot = array();
    // Create the bar plots
    //dispArray($barData);
    $plot=new BarPlot($barData);

    $graph->xaxis->SetTickLabels($xlabels);
    //$plot[1] = new LinePlot($lineData);
    $graph->Add($plot);


    //$title = mb_convert_encoding($title,'UTF-8');
    $graph->title->Set($title);
    //$graph->xaxis->title->Set("X-title");
    //$graph->yaxis->title->Set($yTitle);

    // Display the graph

    //$graph->Stroke();
    $gdImgHandler = $graph->Stroke(_IMG_HANDLER);
    $fileName = "./files/".$filename;
    $graph->img->Stream($fileName);
}
?>
