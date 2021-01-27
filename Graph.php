<?php

class Graph
{
    protected $graph;
    protected $visited = array();

    public function __construct($graph) {
        $this->graph = $graph;
    }

    // find least number of hops (edges) between 2 nodes
    // (vertices)
    public function breadthFirstSearch($origin, $destination, $maxTime) {
        // mark all nodes as unvisited
        foreach ($this->graph as $vertex => $adj) {
            $this->visited[$vertex] = false;
        }

        // create an empty queue
        $q = new SplQueue();

        // enqueue the origin vertex and mark as visited
        $q->enqueue($origin);
        $this->visited[$origin] = true;

        // this is used to track the path back from each node
        $path = array();
        $path[$origin] = new SplDoublyLinkedList();
        $path[$origin]->setIteratorMode(
            SplDoublyLinkedList::IT_MODE_FIFO|SplDoublyLinkedList::IT_MODE_KEEP
        );

        $path[$origin]->push($origin);

        $found = false;
        // while queue is not empty and destination not found
        while (!$q->isEmpty() && $q->bottom() != $destination) {
            $t = $q->dequeue();

            if (!empty($this->graph[$t])) {
                // for each adjacent neighbor
                foreach ($this->graph[$t] as $vertex) {
                    $totalTime = 0;
                    foreach($vertex as $v=>$time){
                        if (!$this->visited[$v]) {
                            // if not yet visited, enqueue vertex and mark
                            // as visited
                            $q->enqueue($v);
                            $this->visited[$v] = true;
                            // add vertex to current path
                            $path[$v] = clone $path[$t];
                            $path[$v]->push([$v => $time]);
                            $totalTime += $time;
                            if($v == $destination){
                                $sep = '';
                                $output = '';
                                $totalTime = 0;
                                foreach ($path[$destination] as $key => $value) {
                                    if($key == 0) {
                                        $output .= $sep. $value;
                                        $sep = '=>';
                                        continue;
                                    }
                                    foreach($value as $vertex => $time){
                                        $output .= $sep. $vertex;
                                        $totalTime += $time;
                                    }

                                }
                                $output .= $sep.$totalTime.PHP_EOL;
                                if($totalTime < $maxTime) {
                                    echo $output;
                                    break;
                                }else {
                                    $this->visited[$v] = false;
                                    unset($path[$v]);
                                }
                            }
                        }
                    }

                }
            }
        }
        //print_r($path);
        if (!isset($path[$destination])) {
            echo "Path not found between $origin to $destination".PHP_EOL;
        }
    }

    public function askForInput(){
        echo "Please enter Device From, Device to and time (Eg: A C 10) followed by Enter Key OR Quit to exit: ".PHP_EOL;
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);
        if(strtolower(trim($line)) == 'quit'){
            fclose($handle);
            echo "ABORTING! Bye-Bye".PHP_EOL;
            exit;
        }else{
            $inputs = explode(' ',$line);
            if(count($inputs) !== 3){
                echo 'Invalid input provided'.PHP_EOL;
                $this->askForInput();
            }
            $this->breadthFirstSearch($inputs[0], $inputs[1], $inputs[2]);
        }
        $this->askForInput();
    }
}

if($argc <= 1){
    echo 'Please provide the path to the input csv file'.PHP_EOL;
    exit;
}

$pathToCsv = $argv[1];

$graph = [];

$row = 1;
if (($handle = fopen($pathToCsv, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
        if(!key_exists($data[0], $graph)){
            $graph[$data[0]] = [
              [$data[1] => $data[2]]
            ];
        }else{
            if(!key_exists($data[1], $graph[$data[0]]))
                $graph[$data[0]][] = [$data[1] => $data[2]];
        }

        if(!key_exists($data[1], $graph)){
            $graph[$data[1]] = [
                [$data[0] => $data[2]]
            ];
        }else{
            if(!key_exists($data[0], $graph[$data[1]]))
                $graph[$data[1]][] = [$data[0] => $data[2]];
        }
    }
    fclose($handle);
}else{
    echo 'Invalid path to the CSV file '.PHP_EOL;
    exit;
}
//print_r($graph);exit;

$g = new Graph($graph);

$g->askForInput();
