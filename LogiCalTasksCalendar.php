<?php
/*
Copyright (c) 2013 Michael Andrew Kaplan

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE. 
*/
 
class LogiCalTasksCalendar {

    private $id;
    private $email;
    
    function __construct($newEmail) { 
        $this->email = $newEmail;
        $this->id = "";
        
        require('db/config.php');
          
        $mysqli = new mysqli($host, $username, $password, $db);
        
        if ($stmt = $mysqli->prepare("SELECT google_calendar_id FROM Calendars WHERE email = ?;")) {
            $stmt->bind_param('s', $this->email);
            $stmt->execute();
            $stmt->bind_result($calId);
            
            if($stmt->fetch()) {
                $this->id = $calId;
            }
            
            $stmt->close();        
        }
        
        $mysqli->close(); 
    }
    
    public function setId($newId) {
        $newId = $newId . "";
        
        if($this->id == "") {
            require('db/config.php');
          
            $mysqli = new mysqli($host, $username, $password, $db);
            
            if ($stmt = $mysqli->prepare("INSERT INTO Calendars (email,  google_calendar_id) VALUES (?,?);")) {
                $stmt->bind_param('ss', $this->email, $newId);
                $stmt->execute();
                
                $stmt->close();        
            }
            
            $mysqli->close(); 
        } else {
            require('db/config.php');
          
            $mysqli = new mysqli($host, $username, $password, $db);
            
            if ($stmt = $mysqli->prepare("UPDATE Calendars SET google_calendar_id = ? WHERE email = ?;")) {
                $stmt->bind_param('ss', $newId, $this->email);
                $stmt->execute();
                
                $stmt->close();        
            }
            
            $mysqli->close();
        }
        
        $this->id = $newId;
    }       

    public function getId() {           
        return $this->id;            
    }

}
?>