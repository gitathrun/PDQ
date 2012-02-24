#!/usr/bin/env ruby
###############################################################################
#  Copyright (C) 1994 - 2012, Performance Dynamics Company                    #
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
# xxx.py
#
# Created by PLH on Thu, Apr 23, 2012

require 'matrix'
require 'pdq'

"
Solve traffic equations numerically using NumPy 

# Traffic equations from PPA p.95:

L0 = 15.36/3600    ... external arrival rate into system
L1 = 0.3L0 + 0.1L3
L2 = 0.7L0 + 0.2L1
L3 = 0.8L1 + 0.9L2

Rearrange terms to produce matrix form:

  1.0L0 + 0.0L1 + 0.0L2 + 0.0L3 = 15.36/3600
- 0.3L0 + 1.0L1 - 0.1L2 + 0.0L3 = 0.0
- 0.7L0 - 0.2L1 + 1.0L2 + 0.0L3 = 0.0
  0.0L0 - 0.8L1 - 0.9L2 + 1.0L3 = 0.0
  
All diagonal coeffs should be 1.0
"

# Don't need line continuation in arrays

a = Matrix[[ 1.0, 0.0, 0.0, 0.0], 
           [-0.3, 1.0,-0.1, 0.0],
           [-0.7,-0.2, 1.0, 0.0],
           [ 0.0,-0.8,-0.9, 1.0]]

ai = a.inverse

printf("AI %s\n" , ai.inspect)

m = ai * a

printf("m %s \n", m.inspect)

# RHS of the traffic eqns

b = Matrix[15.36/3600,0.0,0.0,0.0]

c = ai * b

print c.inspect

# Solve the traffic eqns

L = solve(a,b)
print "Solution: ", L
print "Solution: ", L[0],L[1],L[2],L[3]
print "Check RHS:", dot(a,L)
print "Check L4: ", (0.8*L[1] + 0.9*L[2])

# Visit ratios
v = array([L[0]/L[0], L[1]/L[0], L[2]/L[0], L[3]/L[0]])
print "Visit ratios: ", v[0], v[1], v[2], v[3]

# Service times in seconds
S = array([20, 600, 300, 60])

# Service demands in seconds
D = array([v[0]*S[0], v[1]*S[1], v[2]*S[2], v[3]*S[3]])


"
 Use the service demands derived from the solved traffic equations 
    to parameterize and solve PyDQ queueing model of the passport office
"


# Initialize and solve the model 

pdq.Init("Passport Office");

numStreams = pdq.CreateOpen("Applicant", L[0]);

numNodes = pdq.CreateNode("Window0", pdq.CEN, pdq.FCFS);
numNodes = pdq.CreateNode("Window1", pdq.CEN, pdq.FCFS);
numNodes = pdq.CreateNode("Window2", pdq.CEN, pdq.FCFS);
numNodes = pdq.CreateNode("Window3", pdq.CEN, pdq.FCFS);

pdq.SetDemand("Window0", "Applicant", D[0]);
pdq.SetDemand("Window1", "Applicant", D[1]);
pdq.SetDemand("Window2", "Applicant", D[2]);
pdq.SetDemand("Window3", "Applicant", D[3]);

pdq.Solve(pdq.CANON);

pdq.Report();

# Utilizations: L_0 * D_k

r = array([L[0]*D[0], L[0]*D[1], L[0]*D[2], L[0]*D[3]])

# Queue lengths

printf("U0: %6.2f\tQ0: %6.2f\n", r[0]*100, r[0] / (1 - r[0]))
printf("U1: %6.2f\tQ1: %6.2f\n", r[1]*100, r[1] / (1 - r[1]))
printf("U2: %6.2f\tQ2: %6.2f\n", r[2]*100, r[2] / (1 - r[2]))
printf("U3: %6.2f\tQ3: %6.2f\n", r[3]*100, r[3] / (1 - r[3]))

