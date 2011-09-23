<?php define('SYSPATH', true); header("Content-type: text/html; charset=utf-8");?>
<script type="text/javascript">
function showHide(el){
	if ( nextObject(el).style.display == 'none' ){
		nextObject(el).style.display = 'block';
	} else {
		nextObject(el).style.display = 'none';
	}
}
function nextObject( MyObject){
   do NextObject = MyObject.nextSibling;
   while (NextObject && NextObject.nodeType != 1);
   return NextObject;
}
</script>
<?php
require_once 'inpolygon.class.php';

$points = array(
	array('x'=>70,'y'=>140),
	array('x'=>40,'y'=>140),
	array('x'=>40,'y'=>40),
	array('x'=>40,'y'=>50),
	array('x'=>40,'y'=>80),
	array('x'=>40,'y'=>60),
	array('x'=>20,'y'=>50)
);
$polygon = array(
	array('x'=>10,'y'=>40),
	array('x'=>40,'y'=>50),
	array('x'=>60,'y'=>50),
	array('x'=>120,'y'=>80),
	array('x'=>170,'y'=>60),
	array('x'=>190,'y'=>140),
	array('x'=>150,'y'=>110),
	array('x'=>110,'y'=>150),
	array('x'=>50,'y'=>140),
	array('x'=>10,'y'=>70)
);
echo '<strong onClick="javascript:showHide(this);">Pokaż/ukryj wielokąt:</strong><div style="display:none;"><img src="http://draw.to/static/d/3wOy8v.png?v=ec1c9" /><pre>'; print_r($polygon); echo '</pre></div><br />';

foreach ($points as $index => $point){
	$in_polygon = new inPolygon($point, $polygon);
	
	echo ' ( '.$point['x'].', '.$point['y'].') | Wynik: ';
	if ( $in_polygon->check()){
		echo '<p><strong style="color: green;">YES: Zawiera się  |  </strong></p>';
	} else {
		echo '<p><strong style="color: red;">NO: Nie zawiera się  |  </strong></p>';
	}
	echo '<strong onClick="javascript:showHide(this);">Pokaż/ukryj obiekt:</strong><pre style="display: none;">'; print_r($in_polygon); echo '</pre>';
	echo "<br />\n";
}