<?php
/*
 * ebiz.php
 *
 * Created by NJG: Wed May  8 22:29:36  2002
 * Created by NJG: Fri Aug  2 08:57:31  2002
 *
 * Based on D. Buch and V. Pentkovski, "Experience of Characterization of
 * Typical Multi-tier e-Business Systems Using Operational Analysis,"
 * CMG Conference, Anaheim, California, pp. 671-681, Dec 2002.
 *
 * Measurements use Microsoft WAS (Web Application Stress) tool.
 * Could also use Merc-Interactive LoadRunner.
 * Only a single class of eBiz transaction e.g., login, or page_view, etc.
 * is measured.  Transaction details are not specified in the paper.
 *
 * Thinktime Z should be zero by virtue of N = XR assumption in paper.
 * We find that a Z~27 mSecs is needed to calibrate thruputs and utilizations.
 *
 * PHP5 Translation by Samuel Zallocco - University of L'Aquila - ITALY
 * e-mail: samuel.zallocco@univaq.it
 *
 */

require_once "..\..\..\Lib\PDQ_Lib.php";

error_reporting(0); //  Turning off all error due to division by zero generated by $u1dat, $u2dat and $u3dat values

//-------------------------------------------------------------------------
define("MAXUSERS",20);

function main()
{
	global $job;

	$model = "Middleware I";
	$work = "eBiz-tx";
	$node1 = "WebServer";
	$node2 = "AppServer";
	$node3 = "DBMServer";
	$think = 0.0 * 1e-3;  // treated as free param

	// Added dummy servers for calibration

	$node4 = "DummySvr";

	// User loads employed in WAS tool ...

	$noNodes = 0;
	$noStreams = 0;
	$users = 0;

	$u1pdq = array(); // double [MAXUSERS+1];
	$u2pdq = array(); // double [MAXUSERS+1];
	$u3pdq = array(); // double [MAXUSERS+1];
	$u1err = array(); // double [MAXUSERS+1];
	$u2err = array(); // double [MAXUSERS+1];
	$u3err = array(); // double [MAXUSERS+1];

	// Utilization data from the paper ...

	// In this example the following vectros contain zero value, so on line 106/107/108 can cause division by zero error!!
    $u1dat = array(0.0, 21.0, 41.0, 0.0, 74.0, 0.0, 0.0, 95.0, 0.0, 0.0, 96.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 96.0); // double [MAXUSERS+1]
	$u2dat = array(0.0, 8.0, 13.0, 0.0, 20.0, 0.0, 0.0, 23.0, 0.0, 0.0, 22.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 22.0); // double [MAXUSERS+1]
	$u3dat = array(0.0, 4.0, 5.0, 0.0, 5.0, 0.0, 0.0, 5.0, 0.0, 0.0, 6.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 6.0); // double [MAXUSERS+1]


	// Output header ...

	printf("\n");
	printf("(Tx: \"%s\" for \"%s\")\n\n", $work, $model);
	printf("Client delay Z=%5.2f mSec. (Assumed)\n\n", $think * 1e3);
	printf("%3s\t%6s  %6s   %6s  %6s  %6s\n"," N ", "  X  ", "  R  ", "%Uws", "%Uas", "%Udb");
	printf("%3s\t%6s  %6s   %6s  %6s  %6s\n","---", "------", "------", "------", "------", "------");

	for ($users = 1; $users <= MAXUSERS; $users++) {

		PDQ_Init($model);

		$noStreams = PDQ_CreateClosed($work, TERM, (float) $users, $think);

		$noNodes = PDQ_CreateNode($node1, CEN, FCFS);
		$noNodes = PDQ_CreateNode($node2, CEN, FCFS);
		$noNodes = PDQ_CreateNode($node3, CEN, FCFS);

		$noNodes = PDQ_CreateNode($node4, CEN, FCFS);
		//$noNodes = PDQ_CreateNode($node5, CEN, FCFS);
		//$noNodes = PDQ_CreateNode($node6, CEN, FCFS);

		// NOTE: timebase is seconds

		PDQ_SetDemand($node1, $work, 9.8 * 1e-3);
		PDQ_SetDemand($node2, $work, 2.5 * 1e-3);
		PDQ_SetDemand($node3, $work, 0.72 * 1e-3);

		// dummy (network) nodes ...

		PDQ_SetDemand($node4, $work, 9.8 * 1e-3);

		PDQ_Solve(EXACT);

		// set up for error analysis of utilzations

		$u1pdq[$users] = PDQ_GetUtilization($node1, $work, TERM) * 100;
		$u2pdq[$users] = PDQ_GetUtilization($node2, $work, TERM) * 100;
		$u3pdq[$users] = PDQ_GetUtilization($node3, $work, TERM) * 100;

		$u1err[$users] = 100 * ($u1pdq[$users] - $u1dat[$users]) / $u1dat[$users]; // cause division by zero error due to u1dat vector initialization
		$u2err[$users] = 100 * ($u2pdq[$users] - $u2dat[$users]) / $u2dat[$users]; // cause division by zero error due to u2dat vector initialization
		$u3err[$users] = 100 * ($u3pdq[$users] - $u3dat[$users]) / $u3dat[$users]; // cause division by zero error due to u3dat vector initialization

		printf("%3d\t%6.2f  %6.2f   %6.2f  %6.2f  %6.2f\n",$users,
			   PDQ_GetThruput(TERM, $work),  // http GETs-per-Second
			   PDQ_GetResponse(TERM, $work) * 1e3,   // milliseconds
			   $u1pdq[$users],$u2pdq[$users],$u3pdq[$users]);
	}; // for user

	printf("\nError Analysis of Utilizations\n\n");
	printf("%3s\t%12s  %12s  %12s\n",
		   "   ",
		   "          WS          ",
		   "          AS          ",
		   "          DB          ");
	printf("%3s\t%12s  %12s  %12s\n",
		   "   ",
		   "----------------------",
		   "----------------------",
		   "----------------------");

	printf("%3s    ", " N ");
	printf("%6s  %6s  %6s  ", "%Udat", "%Updq", "%Uerr");
	printf("%6s  %6s  %6s  ", "%Udat", "%Updq", "%Uerr");
	printf("%6s  %6s  %6s\n", "%Udat", "%Updq", "%Uerr");
	printf("%3s    ", "---");
	printf("%6s  %6s  %6s  ", "-----", "-----", "-----");
	printf("%6s  %6s  %6s  ", "-----", "-----", "-----");
	printf("%6s  %6s  %6s\n", "-----", "-----", "-----");

	for ($users = 1; $users <= MAXUSERS; $users++) {
		switch ($users) {
			case 1:
			case 2:
			case 4:
			case 7:
			case 10:
			case 20:
				printf("%3d\t%5.2f\t%5.2f\t%5.2f",
				   	$users,
				   	$u1dat[$users],
				   	$u1pdq[$users],
				   	$u1err[$users]);
				printf("\t%5.2f\t%5.2f\t%5.2f",
				   	$u2dat[$users],
				   	$u2pdq[$users],
				   	$u2err[$users]);
				printf("\t%5.2f\t%5.2f\t%5.2f\n",
				   	$u3dat[$users],
				   	$u3pdq[$users],
				   	$u3err[$users]);
				break;
			default:
				break;
		}
	}

	printf("\n");

	// Uncomment the following line for a standard PDQ summary.

	PDQ_Report();

};  // main

main();

?>