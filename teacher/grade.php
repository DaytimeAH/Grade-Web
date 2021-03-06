<?
require_once(dirname(__FILE__).'/../config.php');
require_once(WebRoot."/login/loginLib.php");
require_once(WebRoot."/lib/mysql.php");

?>
  <!DOCTYPE html>
  <html>

  <head>
    <!--Import materialize.css-->
    <title>学生成绩-教师-学生成绩管理系统</title>
  <link rel="shortcut icon" href="../icons/material-design-icons/action/1x_web/ic_account_circle_black_48dp.png" size="32x32">
  <link rel="icon" href="../icons/material-design-icons/action/1x_web/ic_account_circle_black_48dp.png" sizes="32x32"><link type="text/css" rel="stylesheet" href="../asset/materialize/css/materialize.min.css" media="screen,projection" />
    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  </head>

  <body class=" grey lighten-3">
    <nav>
      <div class="nav-wrapper">
        <a href="#" class="brand-logo center">教师</a>
        <ul id="nav-moblie" class="right hide-on-med-and-down">
          <li><a href="../login/index.php" onClick="delAllCookie();">登出</a></li>
        </ul>
      </div>
    </nav>
    <div class="container">
      <br>
      <h4 class="center">这是学生<?echo $_GET["User"];?>的成绩</h4>
      <br>
      <?
$isEdit=false;
$User=0;
if (isset($_GET["User"])) $User=$_GET["User"];
if (isLogin() && getUserType()==1){
    $isEdit=false;
    $User=getUserID();
}else{
    $isEdit=true;
	  $Name=getName($User);
}?>

        <table id="grade" class="centered white z-depth-3">
          <thead>
            <tr>
              <th type="number">编号</th>
              <th>课程名</th>
              <th type="number">绩点</th>
              <th type="number">成绩</th>
            </tr>
          </thead>
          <tbody>
            <?
$nowSubjectArray=$database->select("subject",["id","name","GPA"],[]);
if ($User!=0 && $database->has("user",["AND"=>["user"=>$User,"type"=>1]])){
    foreach($nowSubjectArray as $nowSuject){
        if (!$database->has("subjectBel",["AND" =>["user" => trim($User),'subject'=>trim($nowSuject["id"])]])) continue;
        $sbScore=$database->get("grade", "score", ["AND" =>["user" => trim($User),'subject'=>trim($nowSuject["id"])]]);
        ?>
              <tr>
                <td>
                  <?echo $nowSuject["id"];?>
                </td>
                <td>
                  <?echo $nowSuject["name"];?>
                </td>
                <td>
                  <?echo $nowSuject["GPA"];?>
                </td>
                <td>
                  <button class="waves-effect waves-teal btn-flat" type="button" id="edit" <? if ($isEdit){ ?>onClick="onEdit(<?
					echo $nowSuject["id"];
					echo ',\'';
					echo $nowSuject["name"];
					echo '\',';
					echo $User;
					echo ',\'';
					echo $Name;
					echo '\',';
					if (!is_numeric($sbScore)) echo '100'; else echo $sbScore;
                ?>);"
                      <?
        }else{
            ?>onClick="denied();"
                        <?}?>>
                          <? if (!is_numeric($sbScore)) echo '-'; else echo $sbScore;	?>
                  </button>
                </td>
              </tr>

              <?}}else echo '<div class="center">No Such a Student.</br>Maybe Error.</div>'?>
          </tbody>
        </table>
    </div>
    </div>

    <!-- Edit Modal Structure -->
    <div id="modalEdit" class="modal modal-fixed-footer">
      <div class="modal-content">
        <h4 class="center"><div id="Edit_Title">Edit Score</div></h4>
        <div class="row">
          <div class="input-field col s10 offset-s1">
            <input disabled value="0" id="Edit_ID" type="text" class="validate">
            <label for="Edit_ID">课程ID</label>
          </div>
        </div>
        <div class="row">
            <div class="input-field col s10 offset-s1">
              <input disabled value="0" id="Edit_Name" type="text" class="validate">
              <label for="Edit_Name">学生姓名</label>
            </div>
        </div>
        <div class="row">
          <div class="input-field col s10 offset-s1">
            <input disabled value="0" id="Edit_User" type="text" class="validate">
            <label for="Edit_User">学号</label>
          </div>
        </div>
        <div class="row">
          <div class="input-field col s10 offset-s1">
            <input id="Edit_Score" type="text" class="validate" value="0">
            <label for="Edit_Score">分数</label>
          </div>
        </div>
      </div>
      <div class="modal-footer">

        <a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat">取消</a>
        <a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat" onClick="editScore();">确定</a>

        <a href="#!" class="modal-action modal-close waves-effect waves-red btn-flat tooltipped" data-position="bottom" data-delay="50" data-tooltip="请三思而后行！" onClick="deleteScore();">删除</a>
      </div>
    </div>
    </div>
    <!--Import jQuery before materialize.js-->
    <script type="text/javascript" src="../asset/js/jquery.js"></script>
    <script type="text/javascript" src="../asset/materialize/js/materialize.min.js"></script>

    <!--Sort function starts-->
    <script type="text/javascript">
      $(function() {
        var tableObject = $('#grade'); //获取id为tableSort的table对象
        var tbHead = tableObject.children('thead'); //获取table对象下的thead
        var tbHeadTh = tbHead.find('tr th'); //获取thead下的tr下的th
        var tbBody = tableObject.children('tbody'); //获取table对象下的tbody
        var tbBodyTr = tbBody.find('tr'); //获取tbody下的tr

        var sortIndex = -1;

        tbHeadTh.each(function() { //遍历thead的tr下的th
          var thisIndex = tbHeadTh.index($(this)); //获取th所在的列号
          //给表态th增加鼠标位于上方时发生的事件
          $(this).mouseover(function() {
            tbBodyTr.each(function() { //编列tbody下的tr
              var tds = $(this).find("td"); //获取列号为参数index的td对象集合
              $(tds[thisIndex]).addClass("hover"); //给列号为参数index的td对象添加样式
            });
          }).mouseout(function() { //给表头th增加鼠标离开时的事件
            tbBodyTr.each(function() {
              var tds = $(this).find("td");
              $(tds[thisIndex]).removeClass("hover"); //鼠标离开时移除td对象上的样式
            });
          });

          $(this).click(function() { //给当前表头th增加点击事件
            var dataType = $(this).attr("type"); //点击时获取当前th的type属性值
            checkColumnValue(thisIndex, dataType);
          });
        });

        $("tbody tr").removeClass(); //先移除tbody下tr的所有css类
        //table中tbody中tr鼠标位于上面时添加颜色,离开时移除颜色
        $("tbody tr").mouseover(function() {
          $(this).addClass("hover");
        }).mouseout(function() {
          $(this).removeClass("hover");
        });

        //对表格排序
        function checkColumnValue(index, type) {
          var trsValue = new Array();

          tbBodyTr.each(function() {
            var tds = $(this).find('td');
            //获取行号为index列的某一行的单元格内容与该单元格所在行的行内容添加到数组trsValue中
            trsValue.push(type + ".separator" + $(tds[index]).html() + ".separator" + $(this).html());
            $(this).html("");
          });

          var len = trsValue.length;

          if (index == sortIndex) {
            //如果已经排序了则直接倒序
            trsValue.reverse();
          } else {
            for (var i = 0; i < len; i++) {
              //split() 方法用于把一个字符串分割成字符串数组
              //获取每行分割后数组的第一个值,即此列的数组类型,定义了字符串\数字\Ip
              type = trsValue[i].split(".separator")[0];
              for (var j = i + 1; j < len; j++) {
                //获取每行分割后数组的第二个值,即文本值
                value1 = trsValue[i].split(".separator")[1];
                //获取下一行分割后数组的第二个值,即文本值
                value2 = trsValue[j].split(".separator")[1];
                //接下来是数字\字符串等的比较
                if (type == "number") {
                  value1 = value1 == "" ? 0 : value1;
                  value2 = value2 == "" ? 0 : value2;
                  if (parseFloat(value1) > parseFloat(value2)) {
                    var temp = trsValue[j];
                    trsValue[j] = trsValue[i];
                    trsValue[i] = temp;
                  }
                } else if (type == "ip") {
                  if (ip2int(value1) > ip2int(value2)) {
                    var temp = trsValue[j];
                    trsValue[j] = trsValue[i];
                    trsValue[i] = temp;
                  }
                } else {
                  if (value1.localeCompare(value2) > 0) { //该方法不兼容谷歌浏览器
                    var temp = trsValue[j];
                    trsValue[j] = trsValue[i];
                    trsValue[i] = temp;
                  }
                }
              }
            }
          }

          for (var i = 0; i < len; i++) {
            $("tbody tr:eq(" + i + ")").html(trsValue[i].split(".separator")[2]);
          }

          sortIndex = index;
        }

        //IP转成整型
        function ip2int(ip) {
          var num = 0;
          ip = ip.split(".");
          num = Number(ip[0]) * 256 * 256 * 256 + Number(ip[1]) * 256 * 256 + Number(ip[2]) * 256 + Number(ip[3]);
          return num;
        }

      })
    </script>
    <!--Sort function ends-->


    <script type="text/javascript">
      $(document).ready(function() {
        // the "href" attribute of .modal-trigger must specify the modal ID that wants to be triggered
        $('.modal').modal();
      });

	function onEdit($sb_id,$sb_name,$user,$name,$score){
        $("#Edit_ID").val($sb_id);
        $("#Edit_Title").html($sb_name);
        $("#Edit_Score").val($score);
        $("#Edit_User").val($user);
        $("#Edit_Name").val($name);
        $("#modalEdit").modal("open");
	}

      function editScore() {
        $.post("_grade.php", {
              "subject":$("#Edit_ID").val(),
              "user":$("#Edit_User").val(),
              "score":$("#Edit_Score").val(),
              "name":$("#Edit_Name").val(),
              "sb_name":$("#Edit_Title").html(),
              "type":1
          },
          function(data, status) {
            if (data == 0) {
              Materialize.toast('No Edition.', 1000);
            } else if (data > 0) {
              Materialize.toast('Edited.', 1000);
              window.location.href = "";
            } else {
              Materialize.toast('Error.', 1000);
            }
          });
      }

      function denied() {
        Materialize.toast('Edit Denied.', 1000);
      }

      function deleteScore() {
        $.post("_grade.php", {
            "subject": $("#Edit_ID").val(),
            "user": $("#Edit_User").val(),
            "score": $("#Edit_Score").val(),
            "type": 3
          },
          function(data, status) {
            if (data > 0) {
              Materialize.toast('Deleted.', 1000);
              window.location.href = "";
            } else {
              Materialize.toast('Error.', 1000);
            }
          });
      }


      //删除cookie中所有定变量函数
      function delAllCookie() {
        $.post("../login/loginLib.php", {
            "logout": true
          },
          function(data, status) {
            window.location.href = "";
          });
      }
    </script>
  </body>

  </html>