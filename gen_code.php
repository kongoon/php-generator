<?php include 'header.php';?>
﻿<?php

/**************************
PHP Code Generator v1.2
Author: Mr.Manop Kongoon
Organization: Programmer Thailand
Contact: contact@programmerthailand.com
***************************/

$dbhost = "localhost";
$dbuser = "root"; //ชื่อผู้ใช้ฐานข้อมูล
$dbpass = ""; //รหัสผ่าน
$dbname = "php_pdo_training"; //ชื่อฐานข้อมูล

$conn = new mysqli($dbhost,$dbuser,$dbpass,$dbname);

//set encoding to utf8
$conn->query('SET character_set_client=utf8');
$conn->query('SET character_set_connection=utf8');
$conn->query('SET character_set_results=utf8');


$setfile = 'admin';//set prefix of file to generate
$setheaderfile = 'head.php';
$setfooterfile = 'foot.php';
$setfunctionfile = 'function.php';
$setdbconnectionfile = 'connect.php';


$txt = "";
$gen_result = "";
$status = $conn->query('SHOW TABLE STATUS FROM '.$dbname); //select table with information
/*echo '<pre>';
foreach($status as $s){
    print_r($s);
}
echo '</pre>';*/
//print_r($status);

foreach($status as $r){//loop for define detail of each table

    //print_r($r);
    //echo '<hr/>';
    //echo 'Table: '.$r['Tables_in_'.$dbname.''].'<br/>';
    //echo 'List of Fields<br/>';
    $table_name = $r['Name'];
    //echo $table_name;
    $table_prefix = substr($r['Name'],0,1); //select first character of table only
    //echo $table_prefix;



    //$rr = $conn->query("DESC ".$table_name);
    //print_r($rr);
    $file_name = $setfile.'_'.$table_name.'.php';//Set filename to create in bottom
    $result = $conn->query('SELECT * FROM '.$r['Name'].'');
    $full_column = $conn->query('SHOW FULL COLUMNS FROM '.$r['Name'].'');

$txt .= "<?php
if(!isset(\$_SESSION)){
    session_start();
}
include '".$setfunctionfile."';
is_".$setfile."();
include '".$setheaderfile."';
include '".$setdbconnectionfile."';

";
    $insert1 = "";
    $insert2 = "";
    $insert3 = "";
    $edit1 = "";
    $edit2 = "";
    $edit3 = "";
    $insert_form = "";
    $list_data1 = "";
    $list_data2 = "";

    $field_nums = count($result->fetch_fields());
    //echo $field_nums.'<br/>';
    $i=1;


    foreach($full_column as $f){ //prepare code for each field
    /*
        Array
        (
            [Field] => id
            [Type] => int(11)
            [Collation] =>
            [Null] => NO
            [Key] => PRI
            [Default] =>
            [Extra] => auto_increment
            [Privileges] => select,insert,update,references
            [Comment] =>
        )
        */
        //print_r($f);
        //Check if Comment Empty, used first capital field name.
        if(empty($f['Comment'])){
            $label = ucfirst($f['Field']);//empty label first capital
        }else{
            $label = $f['Comment'];
        }

        //echo $f->name.'<br/>'; //field name item


        if($f['Field']!='id'){

            //if Not Null value of field
            if($f['Null']=="NO"){
                $notnull = 'required="required"';
            }else{
                $notnull = '';
            }

            //remove , from end of sql statement
            $txt .="\$".$f['Field']."\t= null; // กำหนดค่าเริ่มต้นของ \$".$f['Field']."\n";
            if($i<$field_nums){
                $suffix = ",";
            }else{
                $suffix = "";
            }


            //echo $f['Comment'];
            $insert1 .= $f['Field'].$suffix."\n\t\t\t\t\t";
            $insert2 .= ":".$f['Field']."".$suffix."\n\t\t\t\t\t";
            $insert3 .= "'".$f['Field']."'=>\$".$table_prefix."['".$f['Field']."']".$suffix."\n\t\t\t\t\t";
            $edit1 .="\$".$f['Field']." = \$rse['".$f['Field']."'];\n\t";
            $edit2 .="".$f['Field']."=:".$f['Field']."".$suffix."\n\t\t\t";
            $edit3 .="'".$f['Field']."'=>\$".$table_prefix."['".$f['Field']."']".$suffix."\n\t\t\t\t\t\t";
            //echo $f['Type'];

            if($f['Type']=='text'){//if type is text_
                $form_obj = "<textarea name=\"".$table_prefix."[".$f['Field']."]\" class=\"form-control\" rows=\"10\"  ".$notnull."><?php echo \$".$f['Field'].";?></textarea>";
            }else if(substr($f['Type'],0,4)=='enum'){
                preg_match('/enum\((.*)\)$/', $f['Type'], $matches);
                $vals = explode(',', $matches[1]);

                //print_r($vals);
                $form_obj = '<select name="'.$table_prefix.'['.$f['Field'].']" class="form-control" id="'.$table_prefix."-".$f['Field'].'">'."\n";
                foreach($vals as $v){
                    $form_obj .= "\t\t\t\t".'<option value="'.str_replace("'", "", $v).'" <?php if($'.$f['Field'].'==\''.str_replace("'", "", $v).'\'){?> selected="selected"<?php }?>>'.str_replace("'", "", $v).'</option>'."\n";
                }
                $form_obj .="\t\t\t".'</select>'."\n";
            }else{
                //if foreign key
                if(substr($f['Field'],-3)=='_id'){
                    $foreign_table = explode('_id',$f['Field']);
                    //echo $foreign_table[0];
                    $short_foreigh_table = substr($foreign_table[0],0,2);
                    $form_obj = '<select name="'.$table_prefix.'['.$f['Field'].']" class="form-control" id="'.$table_prefix."-".$f['Field'].'">
                        <option>เลือก'.$label.'</option>
                        <?php
                        $'.$foreign_table[0].'=$con->prepare("SELECT * FROM '. $foreign_table[0].'");
                        $'.$foreign_table[0].'->execute();
                        //print_r($'.$foreign_table[0].');
                        while($'.$short_foreigh_table.' = $'.$foreign_table[0].'->fetch()){?>
                            <option value="<?php echo $'.$short_foreigh_table.'[\'id\'];?>" <?php if($'.$f['Field'].'==$'.$short_foreigh_table.'[\'id\']){?> selected="selected"<?php }?>><?php echo $'.$short_foreigh_table.'[1];?></option>
                        <?php }?>
                    </select>';
                }else{
                    $form_obj = "<input id=\"".$table_prefix."-".$f['Field']."\" class=\"form-control\" type=\"text\" name=\"".$table_prefix."[".$f['Field']."]\" value=\"<?php echo \$".$f['Field'].";?>\" ".$notnull.">";
                }
            }
            $insert_form .= "
    <div class=\"form-group\">
        <label class=\"control-label col-md-2\" for=\"".$table_prefix."-".$f['Field']."\">".$label."</label>
        <div class=\"col-md-10\">
            ".$form_obj."
        </div>
    </div>";

$list_data1 .="\t\t<th>".$label."</th>\n\t";
$list_data2 .="\t\t<td><?php echo \$rs['".$f['Field']."'];?></td>\n\t";

        }
        $i++;
        //echo $i.'<br/>';
    }//loop field



//Text for insert statement
$txt .="
################### การเพิ่มข้อมูล ###############
if(isset(\$_POST['".$table_prefix."']['action']) && \$_POST['".$table_prefix."']['action']=='insert'){//หากมีการกำหนด ".$table_prefix."['action'] และ ".$table_prefix."['action']=='insert' ให้เพิ่มข้อมูล
    \$".$table_prefix." = \$_POST['".$table_prefix."'];
    \$sqli = \"INSERT INTO ".$table_name."(
                    ".$insert1.") VALUES(
                    ".$insert2."
                )\";//คำสั่งในการเพิ่มข้อมูลลงในตาราง ".$table_name."
    \$resulti = \$con->prepare(\$sqli);//เตรียมคำสั่ง SQL
    \$resulti->execute(array(
                    ".$insert3."
                )); //ทำการ Bind ค่าลงใน Field ต่างๆ และประมวลผล
    if(\$resulti!==false){
        \$_SESSION['flash']['type']='success';
        \$_SESSION['flash']['msg']='เพิ่มข้อมูลเรียบร้อย';
    }else{
        \$_SESSION['flash']['type']='danger';
        \$_SESSION['flash']['msg']='ไม่สามารถเพิ่มข้อมูลได้';
    }

}
";
$txt .="
################### การแก้ไขข้อมูล #################
if(isset(\$_GET['action']) && \$_GET['action']=='edit'){ //ถ้ามีการคลิกแก้ไข
    \$".$table_prefix."id = \$_GET['id'];

    \$sqle = \"SELECT * FROM ".$table_name." WHERE id=:".$table_prefix."id\"; //เรียกข้อมูลที่ต้องการแก้ไขมา 1 แถว
    \$resulte = \$con->prepare(\$sqle);//เตรียมคำสั่ง SQL
    \$resulte->execute(array('".$table_prefix."id'=>\$".$table_prefix."id));//ทำการ Bind ค่าลงใน Field ต่างๆ และประมวลผล
    \$rse = \$resulte->fetch(); //เก็บไว้ในตัวแปร \$rse แบบ array()

    ".$edit1." // กำหนดค่าให้กับตัวแปรเพื่อส่งให้ฟอร์ม
}

if(isset(\$_POST['".$table_prefix."']['action']) && \$_POST['".$table_prefix."']['action']=='edit'){// ตรวจสอบว่ามีการส่งค่ามาจากการแก้ไขหรือไม่
    \$".$table_prefix." = \$_POST['".$table_prefix."'];
    \$sqlu = \"UPDATE ".$table_name." SET
            ".$edit2."
            WHERE id=:id\";//คำสั่งในการแก้ไขข้อมูล
    \$resultu = \$con->prepare(\$sqlu);//เตรียมคำสั่ง SQL
    \$resultu->execute(array(
                        'id'=>\$".$table_prefix."['id'],
                        ".$edit3."
                    )
                );// ทำการ Bind ค่าลงใน Field ต่างๆ และประมวลผล
    if(\$resultu!==false){
        \$_SESSION['flash']['type']='success';
        \$_SESSION['flash']['msg']='แก้ไขข้อมูลเรียบร้อย';
    }else{
        \$_SESSION['flash']['type']='danger';
        \$_SESSION['flash']['msg']='ไม่สามารถแก้ไขข้อมูลได้';
    }
}
";


$txt .="
################### การลบข้อมูล ###############
if(isset(\$_GET['action'])&& \$_GET['action']=='delete'){//หากมีการกำหนด action=='delete' ให้ลบข้อมูล
    \$sqld = \"DELETE FROM ".$table_name." WHERE id=:id\";//คำสั่งในการลบข้อมูล
    \$resultd = \$con->prepare(\$sqld);//เตรียมคำสั่ง SQL
    \$resultd->execute(array('id'=>\$_GET['id']));//ทำการ Bind ค่าลงใน Field ต่างๆ และประมวลผล
    if(\$resultd!==false){
        \$_SESSION['flash']['type']='success';
        \$_SESSION['flash']['msg']='ลบข้อมูลเรียบร้อยแล้ว';
    }else{
        \$_SESSION['flash']['type']='danger';
        \$_SESSION['flash']['msg']='ไม่สามารถลบข้อมูลได้';
    }
}

################### เลือกข้อมูลมาแสดงในตาราง ###############
\$sql = \"SELECT * FROM ".$table_name." ORDER BY id DESC\";//คำสั่งในการเลือกข้อมูล
\$result = \$con->prepare(\$sql);//เตรียมคำสั่ง SQL
\$result->execute();//ประมวลผล
?>
";


$txt .="
<!-- ############### การแจ้งเตือน ############# -->
<?php if(isset(\$_SESSION['flash'])){ ?>
<div class=\"alert alert-<?php echo \$_SESSION['flash']['type'];?>\">
    <?php echo ucfirst(\$_SESSION['flash']['type']).' '.\$_SESSION['flash']['msg'];?>
</div>
<?php }?>
<!--################ แบบฟอร์มกรอกข้อมูล ############## -->
<div class=\"row\">
<div class=\"col-md-12\">
<h3>".$r['Comment']."</h3>
<form method=\"post\" action=\"<?php echo \$_SERVER['PHP_SELF'];?>\" class=\"form-horizontal\">
<?php if(isset(\$_GET['action']) && \$_GET['action']=='edit'){?>
    <input type=\"hidden\" name=\"".$table_prefix."[action]\" value=\"edit\">
    <input type=\"hidden\" name=\"".$table_prefix."[id]\" value=\"<?php echo \$".$table_prefix."id;?>\">
<?php }else{?>
    <input type=\"hidden\" name=\"".$table_prefix."[action]\" value=\"insert\">
<?php }?>
    ".$insert_form."
        <input type=\"submit\" value=\"บันทึกข้อมูล ".$r['Comment']."\" class=\"btn btn-primary\">
        <?php if(isset(\$_GET['action']) && \$_GET['action']=='edit'){ //หากมีการแก้ไขให้แสดงปุ่ม ยกเลิก ?>
            <a href=\"".$file_name."\" class=\"btn btn-warning\">ยกเลิก</a>
        <?php }?>

</form>
</div>
";



$txt .="
<hr />
<div class=\"col-md-12\">
<!-- ############### รายการข้อมูล ############# -->
<h3>รายการ".$r['Comment']."</h3>
<div class=\"table-responsive\">
<table class=\"table table-bordered table-hover table-striped\">
    <thead>
        <tr>
    ".$list_data1."
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php while(\$rs=\$result->fetch()){?>
        <tr>
    ".$list_data2."
            <td>
                <a href=\"<?php echo \$_SERVER['PHP_SELF'];?>?action=edit&id=<?php echo \$rs['id'];?>\" class=\"btn btn-xs btn-warning\">แก้ไข</a>
                <a href=\"<?php echo \$_SERVER['PHP_SELF'];?>?action=delete&id=<?php echo \$rs['id'];?>\" class=\"btn btn-xs btn-danger\" onclick=\"return confirm('แน่ใจนะว่าต้องการลบ?');\">ลบ</a>
            </td>
        </tr>
    <?php }?>
    </tbody>
</table>
</div>
</div>
</div><!--row-->
<?php
include '".$setfooterfile."';
?>
";
//echo $txt;
?>

<?php
    $fo1 = fopen($file_name, 'w');
    fwrite($fo1,$txt);

    if($fo1){
        $gen_result .="สร้างไฟล์ ".$file_name." สำเร็จ<br/>";
    }else{
        $gen_result .="ไม่สามารถสร้างไฟล์ ".$file_name." ได้";
    }

    fclose($fo1);
$txt = "";

    //we will generate file here!
}//loop fetch table or main loop

//echo $txt;

?>
<h2>ผลการสร้างไฟล์</h2>
<?php echo $gen_result;?>

<p><a href="index.php" class="btn btn-success">ดูการเปลี่ยนแปลง</a>
</p>
<?php
$gen_result = "";
include 'footer.php';?>
