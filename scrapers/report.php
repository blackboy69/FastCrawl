<?
include_once "config.inc";

report();
function report()
{
	$types = db::oneCol("SELECT distinct type from load_queue");
	$output = "";

	foreach($types as $type)
	{
		############
		$total = db::oneCell("SELECT count(*) from load_queue where type='$type'");
		$processed = db::oneCell("SELECT count(*) from load_queue where type='$type' and processing=1");
		$unProcessed = $total-$processed;
		$lq_percent = sprintf("%.2f" , $processed/$total*100);		
		$p_over_t = number_format($processed)." of ".number_format($total) ;
		$load_queue =  str_pad("$lq_percent%",8	). " $p_over_t urls downloaded" ;

		##################
		$total = db::oneCell("SELECT count(*) from raw_data where type='$type'");
		$processed = db::oneCell("SELECT count(*) from raw_data where type='$type' and parsed=1");
		$unProcessed = $total-$processed;
		$rd_percent = sprintf("%.2f" , $processed/$total*100);
		$p_over_t = number_format($processed)." of ".number_format($total) ;
		$raw_data = str_pad("$rd_percent%",8). " $p_over_t pages parsed";;
		###########





		$listings = str_pad(strtoupper($type),30,'.') . number_format(db::oneCell("SELECT count(*) from $type")) . " csv records created";

		if ($rd_percent == 100 && $lq_percent == 100)
		{
			/// show completed?
			$output .= "$listings\n";
			$output .= "   - load_queue $load_queue\n";
			$output .= "   - raw_data   $raw_data\n";			
			$output .= "\n";


		}
		else
		{
			// show uncompleted?
			echo "$listings\n";
			echo "   - load_queue $load_queue\n";
			echo "   - raw_data   $raw_data\n";			
			echo "\n";
		}
	}
	echo $output;
}