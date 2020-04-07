<?php
// クラスタの可視化
// created by H. Sawano

$patients_arr = [];
// ファイルが存在しているかチェックする
if (($handle = fopen("data/patients.csv", "r")) !== FALSE) {
  $isHeaderRead=false;
  // 1行ずつfgetcsv()関数を使って読み込む
  while (($data = fgetcsv($handle))) {
      if ($isHeaderRead==$false) {
          $isHeaderRead = true;
          continue;
      }

      $index=0;
      foreach ($data as $value) {
        switch ($index) {
          case 0:
            $patient_arr["id"] = $value;
            //echo "「${value}」\n";
            break;
          case 2:
            $patient_arr["name"] = $value;
            //echo "「${value}」\n";
            break;
          case 5: //関連
            preg_match('/No.([0-9]*).*と接触/u', $value, $m);
            $patient_arr["relationship"] = $m[1];
            //echo $m[1] . "\n";
          break;
          default;
        }
        $index++;
      }
      //echo "<br>";
      $patients_arr[] = $patient_arr;
  }
  fclose($handle);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>クラスタの可視化</title>
    <script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
</head>
  <body>
    <h1>愛知県のクラスタ可視化</h1>
    created by <a href="https://sawanolab.aitech.ac.jp" target="_blank">H. Sawano</a><br>
    出典: <a href="https://www.pref.aichi.jp/site/covid19-aichi/kansensya-kensa.html" target="_blank">愛知県新型コロナウイルス感染症対策サイト</a>
<style type="text/css">

</style>
<script type="text/javascript">
    var width = 700;
    var height = 700;
    // nodeの定義。ここを増やすと楽しい。
    var nodes = [
        <?php
          foreach ($patients_arr as $value) {
            echo "{id: " . $value['id'] . ", label: '" . $value['id'] . "'}";
            //echo "{id: " . $value['id'] . ", label: '" . "A" . "'}";
            if($value !== end($patients_arr)){
              echo ",\n";
            }
          }
        ?>
        /*
        {id:0, label:"nodeA"},
        {id:1, label:"nodeB"},
        {id:2, label:"nodeC"},
        {id:3, label:"nodeD"},
        {id:4, label:"nodeE"},
        {id:5, label:"nodeF"},
        */
    ];

    // node同士の紐付け設定。実用の際は、ここをどう作るかが難しいのかも。
    var links = [
        <?php
        foreach ($patients_arr as $value) {
            if ($value["relationship"] == "") {
              continue;
            }
            echo "{source: " . $value['relationship'] . ", target: " . $value['id'] . "}";
            if($value !== end($patients_arr)){
              echo ",\n";
            }
        }
        ?>
    ];
    // forceLayout自体の設定はここ。ここをいじると楽しい。
    var force = d3.layout.force()
        .nodes(nodes)
        .links(links)
        .size([width, height])
        .distance(40) // node同士の距離
        .friction(0.9) // 摩擦力(加速度)的なものらしい。
        .charge(-60) // 寄っていこうとする力。推進力(反発力)というらしい。
        .gravity(0.1) // 画面の中央に引っ張る力。引力。
        .start();

    // svg領域の作成
    var svg = d3.select("body")
        .append("svg")
        .attr({width:width, height:height});

    // link線の描画(svgのline描画機能を利用)
    var link = svg.selectAll("line")
        .data(links)
        .enter()
        .append("line")
        .style({stroke: "red",
        "stroke-width": 5
    });

    // nodesの描画(今回はsvgの円描画機能を利用)
    var node = svg.selectAll("circle")
        .data(nodes)
        .enter()
        .append("circle")
        .attr({
            r: 15
        })
        .style({
            fill: "black"
        })
        .call(force.drag);

    // nodeのラベル周りの設定
    var label = svg.selectAll('text')
        .data(nodes)
        .enter()
        .append('text')
        .attr({
            "text-anchor":"middle",
            "fill":"white",
            "font-size": "13px"
        })
        .text(function(data) { return data.label; });

    // tickイベント(力学計算が起こるたびに呼ばれるらしいので、座標追従などはここで)
    force.on("tick", function() {
        link.attr({
            x1: function(data) { return data.source.x;},
            y1: function(data) { return data.source.y;},
            x2: function(data) { return data.target.x;},
            y2: function(data) { return data.target.y;}
        });
        node.attr({
            cx: function(data) { return data.x;},
            cy: function(data) { return data.y;}
        });
        // labelも追随するように
        label.attr({
            x: function(data) { return data.x;},
            y: function(data) { return data.y;}
        });
    });

</script>

</body>
</html>