We loop through the courses 
we pick a course
generate scored dates  (using the helper methods)
begin by picking the first date (attempt start date)
get the student daily load for that date

check
if: the load does not exceed the daily max load
then: 
- generate potential slots for that date using the helper methods 
begin by picking the first slot 
- generate the available invigilators using that date
- generate available halls using that date
if: no halls or no ivigilators generated 
then: return and pick the next slot 
when: all slots have been checked and no match is found 
then we return to the next date and repeat the process if all the dates and the generated slots have been attempted 
and this the said course its unable to be placed the code counts it as an impossible placement and it returns to the next course  



We check whether placing the course on that date exceeds the student daily load range.
If the date is valid:
We generate available slots for that date. (using the helper method as showin in the code)
We fetch potential invigilators for the date. (using the helper method as showin in the code)
We fetch available halls. (using the helper method as showin in the code)
We attempt placement.
If the date exceeds the student daily load:
We try another date.
If all dates exceed the range:
We fallback to the first busy date anyway.
Once a date is selected:
We generate slots for the date. (using the helper method as showin in the code)
We currently pick the first generated slot.
Then assign hall + invigilators.
If placement fails because:
There are no halls, on the first picked time 
we return and pick for the next time 
