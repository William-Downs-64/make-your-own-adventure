<?php
$id = $_GET['id'];
$table = $_GET['table'];

$conn = new mysqli("localhost", "root", "", "cyoa");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// echo "$q";

$sql = "SELECT * FROM $table WHERE `id` = '$id';";

$result = mysqli_query($conn , $sql);


while($row = mysqli_fetch_array($result)) {
  // echo "<tr id='$id'>";
  // echo "<td class='description'>" . $row['area'] . "</td>";
  // if($row['choice1']) {
  // echo "<td class='link'><button class='btn btn-primary' data-choice='1' data-link='" . $row['link1'] ."'>" . $row['choice1'] . "</button></td>";
  // echo "<td>" . $row['link1'] . "</td>";
  // } else {
  //   echo "<td class='link'><button class='btn btn-danger' data-choice='1' data-link='1'>Restart</button></td>";
  // }
  // if ($row['choice2']) {
  // echo "<td class='link'><button class='btn btn-warning' data-choice='2' data-link='" . $row['link2'] ."'>" . $row['choice2'] . "</button></td>";
  // echo "<td>" . $row['link2'] . "</td>";
  // }
  // echo "</tr>";
  $myObj = new stdClass();
  $myObj->id = "$id";
  $myObj->area = $row['area'];
  $myObj->choice1 = $row['choice1'];
  $myObj->link1 = $row['link1'];
  if(str_contains(strtolower($row['area']), "you win!")) {
    $myObj->choice1 = 'Congratulations';
    $myObj->link1 = 'win';
  }
  $myObj->choice2 = $row['choice2'];
  $myObj->link2 = $row['link2'];
  $myObj->author = $row['author'];

  $myObj = json_encode($myObj);

  echo $myObj;

}
// echo "</table>";

mysqli_close($conn);
?>