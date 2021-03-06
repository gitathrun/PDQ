#!/usr/bin/perl
###############################################################################
#  Copyright (C) 1994 - 2009, Performance Dynamics Company                    #
#                                                                             #
#  This software is licensed as described in the file COPYING, which          #
#  you should have received as part of this distribution. The terms           #
#  are also available at http://www.perfdynamics.com/Tools/copyright.html.    #
#                                                                             #
#  You may opt to use, copy, modify, merge, publish, distribute and/or sell   #
#  copies of the Software, and permit persons to whom the Software is         #
#  furnished to do so, under the terms of the COPYING file.                   #
#                                                                             #
#  This software is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY  #
#  KIND, either express or implied.                                           #
###############################################################################

#
# Based on closed_center.c
# 
# Illustrate use of PDQ solver for multiclass workload.
#  
#  $Id: multi_class.pl,v 4.3 2009/03/26 02:55:32 pfeller Exp $
#
#-------------------------------------------------------------------------------

use pdq;


#---- Model specific variables -------------------------------------------------

$think = 0.0;


#---- Initialize the model -----------------------------------------------------

$tech = $pdq::APPROX;

printf "**** %s Solution ****:\n\n", $tech == $pdq::EXACT ? "EXACT" : "APPROX";
printf "  N      R (w1)    R (w2)\n";

for ( $pop = 1;  $pop < 10;  $pop++ ) {
	pdq::Init("Test_Exact_calc");

	#---- Define the workload and circuit type ----------------------------------

	$noStreams = pdq::CreateClosed("w1", $pdq::TERM, 1.0 * $pop, $think);
	$noStreams = pdq::CreateClosed("w2", $pdq::TERM, 1.0 * $pop, $think);

	#---- Define the queueing center --------------------------------------------

	$noNodes = pdq::CreateNode("node", $pdq::CEN, $pdq::FCFS);

	#---- service demand --------------------------------------------------------

	pdq::SetDemand("node", "w1", 1.0);
	pdq::SetDemand("node", "w2", 0.5);

	#---- Solve the model -------------------------------------------------------

	pdq::Solve($tech);

	printf "%3.0f    %8.4f  %8.4f\n", $pop,
	   pdq::GetResponse($pdq::TERM, "w1"),
	   pdq::GetResponse($pdq::TERM, "w2");
}


