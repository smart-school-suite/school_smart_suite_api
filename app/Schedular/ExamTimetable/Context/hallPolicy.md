now this document specifies how available halls are selected,

# Where hall is not busy
1. If the hall is not busy and the hall capacity matches the candidate count we add to the available halls (priority => 1)
2. If the hall is not busy and the hall capacity does not match the candidate count, we find hall combinations that can solve this problem if they are grouped example candidate count 100, hall A capacity (20) hall b capcity (30) hall c capacity (50) so they make a perfect group where (Hall A, Hall B, Hall C) within the groups we can add priority the longer the combination we increase the priority if no combinations are found we can skip that hall the priority of this case is (priority => 2)

# Where hall is not Busy and Hall Is Busy
1. if the hall is not busy but its capacity is exceeded by the candidate count we can find combinations with halls that are busy only and still have space example candidate count 100 find hall capacity 70 then hall E busy available space 10, Hall G busy available space 20 they can form a group with priority please not the busy halls start_time must match the params $startTime (priority => 3) 

# Where hall is Busy
1. The start time of the slot params must match the start time within the hall busy period else its the hall is excluded (priority => 4)
2. If the hall is busy but the hall is not full meaning the current candidates there its not up to the hall capacity we try to see if we can add the candidates inside the hall if it does not exceed the hall capacity (priority => 5)

3. If the hall is busy but the hall is not full meaning the current candidates there its not up to the hall capacity we try to see if we can add the candidates inside the hall if it exceeds the capacity we can find combinations with other busy halls only and rank them the longer the combination increases (priority => 6) 
