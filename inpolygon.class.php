<?php 
/**
 * @author Robert *Vokiel* Mikołajuk vokiel@vokiel.com http://blog.vokiel.com
 * @copyright (c) 2011 Robert Mikołajuk
 */
class inPolygon {
	/**
	 * @var	array	$point	Współrzędne prawdzanego punktu
	 */
	protected $point;
	/**
	 * @var	array	$raypoint	Punkt końcowy półprostej od $this->point równoległej do osi OX 
	 */
	protected $raypoint;
	/**
	 * @var	array	$polygon	Tablica współrzędnych punktów
	 */
	protected $polygon;
	/**
	 * @var	int	$crosses	Liczba przecięć odcinków
	 */
	protected $crosses = 0;

	/**
	 * 
	 * @param array $point
	 * @param array $polygon
	 */
	public function __construct($point,$polygon){
		$this->point = $point;
		$this->raypoint = array('x' => ($point['x']+1), 'y' => $point['y']); 
		$this->polygon = $polygon;

		// jeśli ostatni element nie jest tożsamy z pierwszym, dodajemy go na końcu tablicy
		if ( $this->polygon[0] != $this->polygon[count($this->polygon)-1]){
			array_push($this->polygon,$this->polygon[0]); 
		}
	}
	
	/**
	 * Sprawdzenie czy punkt zawiera się w obszarze
	 * @return bool
	 */
	public function check(){
		// sprawdzenie czy punkt nie należy do jednego z boków wielokąta
		for ($i=0; $i<count($this->polygon); $i++){
			if ( $this->pointCrossEdge($this->polygon[$i],$this->polygon[$i+1],$this->point) ){
				return true;
			}
		}
		$this->setRaypoint();
		$this->edgeCross();
				
		if ($this->crosses % 2 == 1){
			return true;
		}
		return false;
	}
	
	/**
	 * Wyznaczenie punktów półprostej równoległej do osi OX
	 * Współrzędna X punktu P1 musi być większa od największej wpsółrzędnej X wśród wszystkich wierzchołków wielokąta
	 */
	protected function setRaypoint(){
		for ($i=0; $i<count($this->polygon); $i++){
			if ( $this->polygon[$i]['x'] > $this->raypoint['x'] ){
				$this->raypoint['x'] = $this->polygon[$i]['x'];
			}
		}
		$this->raypoint['x'] = $this->raypoint['x']+1;
	}
	
	/**
	 * Wyliczenie ilości przecięć półprostej przez odcinki boków wielokąta 
	 * Półprosta zostaje przeprowadzona od badanego punktu w prawo, 
	 * aż za najbardziej wysunięty w prawo punkt wielokąta  
	 */
	protected function edgeCross() {
		// wstawienie na koniec tablicy punktów wielokąta drugiego wierzchołka - dla ułatwienia obliczeń
		array_push($this->polygon,$this->polygon[1]);
		for ($i=1; $i<(count($this->polygon)-1); $i++){
			// Prosta P-P1 zawiera się w boku wielokąta W($i,$i+1) 
			if ($this->pointCrossEdge($this->point, $this->raypoint, $this->polygon[ $i ]) &&
				$this->pointCrossEdge($this->point, $this->raypoint, $this->polygon[ ($i+1) ]) 
			){
				// Punkt wcześniejszy wielokątea i dalszy leżą po przeciwnej stronie prostej P-P1 - ilość przecięć: 1
				if ($this->sng( $this->det( $this->point, $this->polygon[ $i ], $this->polygon[ ($i-1) ] ) ) != 
					$this->sng( $this->det( $this->point, $this->polygon[ $i ], $this->polygon[ ($i+2) ])) 
				){
					$this->crosses++;
				}
			} else { // Prosta P-P1 nie zawiera się w boku wielokąta
				// Prosta P-P1 zawiera wierzchołek W($i)
				if ($this->pointCrossEdge( $this->point, $this->raypoint, $this->polygon[ $i ] ) &&
					!$this->pointCrossEdge( $this->point, $this->raypoint, $this->polygon[ ($i-1) ] ) &&
					!$this->pointCrossEdge( $this->point, $this->raypoint, $this->polygon[ ($i+1) ] )
				){
					// Sprawdzenie położenia wierzhołków sąsiadujących z wierzchołkiem W($i)
					if ($this->sng( $this->det( $this->point, array('x'=>($this->point['x']+1),'y'=>$this->point['y']), $this->polygon[ ($i-1) ] ) ) !==
						$this->sng( $this->det( $this->point, array('x'=>($this->point['x']+1),'y'=>$this->point['y']), $this->polygon[ ($i+1) ] ) )
					){
						$this->crosses++;
					}
				} else {
					// Sprawdzenie czy prosta P-P1 przecina bok wilokąta W($i,$i+1)
					if ( $this->edgeCrossEdge( $this->polygon[ $i ], $this->polygon[ ($i+1) ], $this->point, $this->raypoint) &&
						(!$this->pointCrossEdge( $this->point, $this->raypoint, $this->polygon[ ($i-1) ] ) ||  
							(
								!$this->pointCrossEdge( $this->point, $this->raypoint, $this->polygon[ ($i-2) ] ) &&
								!$this->pointCrossEdge( $this->point, $this->raypoint, $this->polygon[ ($i-3) ] )
							) && (
								$this->sng( $this->det( $this->point, array('x'=>($this->point['x']+1),'y'=>$this->point['y']), $this->polygon[ ($i-1) ] ) ) !==
								$this->sng( $this->det( $this->point, array('x'=>($this->point['x']+1),'y'=>$this->point['y']), $this->polygon[ ($i-2) ] ) )
							)
						) && !$this->pointCrossEdge( $this->point, $this->raypoint, $this->polygon[ ($i+1) ] )
					){
						$this->crosses++;
					}
				}
			}
		}
	}
	
	/**
	 * 
	 * Sprawdzenie czy $check_point należy do odcinka ($start_point|$stop_point) 
	 * @param array $start_point	Punkt startowy odcinka
	 * @param array $stop_point 	Punkt końcowy odcinka
	 * @param array $check_point	Sprawdzany punkt
	 */
	protected function pointCrossEdge($start_point,$stop_point,$check_point){
		return $this->det($start_point, $stop_point, $check_point) == 0 &&
			min( $start_point['x'], $stop_point['x'] ) <= $check_point['x'] &&
			$check_point['x'] <= max( $start_point['x'], $stop_point['x'] ) &&
			min ( $start_point['y'], $stop_point['y'] ) <= $check_point['y'] &&
			$check_point['y'] <= max( $start_point['y'], $stop_point['y'] );
	}
	
	/**
	 * Sprawdzenie czy odcinki $start_point_1-$stop_point_1 i $start_point_2-$stop_point_2 przecinają się
	 * @param array $start_point_1	Punkt startowy pierwszego odcinka 
	 * @param array $stop_point_1	Punkt końcowy pierwszego odcinka 
	 * @param array $start_point_2	Punkt startowy drugiego odcinka 
	 * @param array $stop_point_2	Punkt końcowy drugiego odcinka 
	 */
	protected function edgeCrossEdge($start_point_1,$stop_point_1,$start_point_2,$stop_point_2){
		return ($this->sng( $this->det($start_point_1,$stop_point_1,$start_point_2) ) != $this->sng( $this->det($start_point_1,$stop_point_1,$stop_point_2) ) && 
				$this->sng( $this->det($start_point_2,$stop_point_2,$start_point_1) ) != $this->sng( $this->det($start_point_2,$stop_point_2,$stop_point_1) ) ||
				$this->pointCrossEdge($start_point_1,$stop_point_1,$start_point_2) ||
				$this->pointCrossEdge($start_point_1,$stop_point_1,$stop_point_2) ||
				$this->pointCrossEdge($start_point_2,$stop_point_2,$start_point_1) ||
				$this->pointCrossEdge($start_point_2,$stop_point_2,$stop_point_1)
		);
	}
	
	/**
	 * Wyznacznik macierzy kwadratowej stopnia 3
	 * @param array $start_point
	 * @param array $stop_point
	 * @param array $check_point
	 */
	protected function det($start_point,$stop_point,$check_point){
		return $start_point['x'] * ( $stop_point['y'] - $check_point['y'] ) + $stop_point['x'] * ( $check_point['y'] - $start_point['y'] ) + $check_point['x'] * ( $start_point['y'] - $stop_point['y']);
		/* druga metoda
		return ($start_point['x'] * $stop_point['y'] + $stop_point['x'] * $check_point['y'] + $check_point['x'] * $start_point['y'] - $check_point['x'] * $stop_point['y'] - $start_point['x'] * $check_point['y'] - $stop_point['x'] * $start_point['y']);
		*/ 
	}
	
	/**
	 * Określenie znaku liczby
	 * @param int $x	Liczba
	 * @return int $x
	 */
	protected function sng($x){
		if ( $x == 0 ){
			return 0;
		} else if ( $x > 0){
			return 1;
		} else {
			return -1;
		}
	}
	
}// end of inPolygon class