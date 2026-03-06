[![Review Assignment Due Date](https://classroom.github.com/assets/deadline-readme-button-22041afd0340ce965d47ae6ef1cefeee28c7c493a6346c4f15d667ab976d596c.svg)](https://classroom.github.com/a/uYH-8BSY)
# CSE3300
REPLACE-THIS-TEXT-WITH-YOUR-NAME-STUDENT-ID-AND-GITHUB-USERNAME

Wonjun Kim - 605200 - wonjun0608

MariaDB [module3group]> SELECT id, username FROM users;
+----+----------------+
| id |      username |
|  2 |      abc      |
|  8 |      alice    |
|  7 |      james    |
|  5 |      todd     |
|  6 |      todd2    |
|  1 |      wonjun   |
|  3 |      youan    |
+----+----------------+

Link of the site: http://ec2-18-222-197-42.us-east-2.compute.amazonaws.com/~wonjun0608/module3group/



<br>
Additonal information : You can log in with this, I uploaded story from those people ID: 

username: alice

password: 3535 

username: james 

password: james12


username: abc

password: abc




<br>

Creative Portion :
1. Like/Unlike funciton

Users can like a story, and if they already liked it, they can undo it by clicking the Unlike button. The total number of likes is displayed below each story, along with a list of the users who liked it, shown in chronological order. To make sure each user can only like a story once, I created a separate story_likes table with a unique rule on (story_id, user_id).

2. User Profile Page
   
When a username is clicked, it links to that person’s profile page, which shows their bio along with all the stories and comments they have posted. If a user clicks on their own name and opens their own profile page, they also have the option to edit and update their bio.

3. Password Change
   
On their own profile, a user can update their password. For security, the user must first enter their current password and then provide a new one. The important point is that only the account owner can change the password. In addition, all passwords are securely hashed and salted using PHP’s built-in password_hash() and verified with password_verify()

4. Timestamps for Stories and Comments

Each story and comment automatically shows the date and time it was uploaded. This was implemented by adding a created_at column with a timestamp type in the database tables (stories and comments), using DEFAULT current_timestamp(). When a new story or comment is inserted, the database automatically records the upload time, which is then displayed on the site. 
For timestap function, ref: https://dev.mysql.com/doc/refman/8.4/en/timestamp-initialization.html





<br><br><br><br><br><br><br><br><br>
Rubric


| Possible | Requirement                                                                      |
| -------- | -------------------------------------------------------------------------------- | 
| 3        | A session is created when a user logs in                                         | 
| 3        | New users can register                                                           | 
| 3        | Passwords are hashed and salted                                                  | 
| 3        | Users can log out                                                                | 
| 8        | User can edit and delete their own stories/comments but not those of other users | 
| 4        | Relational database is configure with correct data types and foreign keys        | 
| 3        | Stories can be posted                                                            | 
| 3        | A link can be associated with each story and is stored in its own database field | 
| 4        | Comments can be posted in association with a story                               | 
| 3        | Stories can be edited and deleted                                                |
| 3        | Comments can be edited and deleted                                               | 
| 3        | Code is well formatted and easy to read                                          |
| 2        | Safe from SQL injection attacks                                                  | 
| 3        | Site follows FIEO                                                                | 
| 2        | All pages pass the W3C validator                                                 | 
| 5        | CSRF tokens are passed when creating, editing, and deleting comments/stories     |
| 4        | Site is intuitive to use and navigate                                            | 
| 1        | Site is visually appealing                                                       |  

## Creative Portion (15 possible)

| Feature | 
| ------- |

## Grade

| Total Possible |
| -------------- |
| 75             |
