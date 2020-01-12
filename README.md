PHP version: 7.1.29
PHPUnit: 7.0.0
IDE: PHPStorm

Run:
	root> php index.php
or
	runing SolutionTest.php using PHPUnit (recommanded)

	
Logic：
	1) check trunk socks
		1.1) if trunk is full, go to Logistics center
		1.2) if not, select branch to go
	2) Select branch according distance and stocks in branch factors
		2.1) if branch has stocks, go to next branch
		2.2) if hasn't stocks, choose the second another branch
	3) Enter branch and load cars in trunk
		3.1) if trunk if not full, load cars
			3.1.1) if trunk has place left, continue step 2)
			3.1.2) if trunk is full, finish collect process
		3.2) if trunk is full, finish collect process
	4) check time constrain and all stocks in all branches
		4.1) time not exceed and if has stocks left, do another tranport tour
		4.2) time not exceed and if has no stocks left, finish work today
		4.3) time exceed, finish work today
		
Result on console  

	Start a new tour... 
		branch 1 has 7, 7 loaded, 0 left 
		branch 1 -> branch: 5
		branch 5 has 7, 1 loaded, 6 left 
		END 5 -> LC! 
	total_time 03:22:30
	Start a new tour... 
		branch 5 has 6, 6 loaded, 0 left 
		branch 5 -> branch: 4
		branch 4 has 1, 1 loaded, 0 left 
		branch 4 -> branch: 2
		branch 2 has 1, 1 loaded, 0 left 
		END 2 -> LC! 
	total_time 07:07:30
	--------------------------------
				Result              
	-------------------------------- 
	Cars collected : 16 
	Tours today : 2
		branch passed : 0 -> 1 -> 5 -> Logistic Center, time : 202.5
		branch passed : 0 -> 5 -> 4 -> 2 -> Logistic Center, time : 225
	Total time : 07h07m
	Time constrain: 06:00:00
	Failed asserting that 25650.0 is less than 21600.
`