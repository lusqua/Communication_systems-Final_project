<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SCO Dashboard</title>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
  <!--suppress JSUnresolvedLibraryURL -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.min.js"></script>
  <!--suppress JSUnresolvedLibraryURL -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.min.js" type="text/javascript"></script>
</head>
<canvas id="Tempchart" width="750" height="375"></canvas>
<canvas id="Umidchart" width="750" height="375"></canvas>
<body>
  <script>
    $(document).ready(function () {
        const configTemp = {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: "Temperatura",
                    backgroundColor: 'rgb(255, 99, 132)',
                    borderColor: 'rgb(255, 99, 132)',
                    data: [],
                    fill: false,
                }],
            },
            options: {
                responsive: true, // Adjust this later
                title: {
                    display: true,
                    text: 'Medições de temperatura e umidade'
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
                scales: {
                    xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Tempo'
                        }
                    }],
                    yAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Valor'
                        }
                    }]
                }
            }
        };

        const configUmid = {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: "Umidade",
                    backgroundColor: 'rgba(102, 181, 250, 98)',
                    borderColor: 'rgba(102, 181, 250, 98)',
                    data: [],
                    fill: false,
                }],
            },
            options: {
                responsive: true, // Adjust this later
                title: {
                    display: true,
                    text: 'Medições de umidade'
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
                scales: {
                    xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Tempo'
                        }
                    }],
                    yAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Porcentagem'
                        }
                    }]
                }
            }
        };

        const tempContext = document.getElementById('Tempchart').getContext('2d');
        const tempChart = new Chart(tempContext, configTemp);

        const umidContext = document.getElementById('Umidchart').getContext('2d');
        const umidChart = new Chart(umidContext, configUmid);

        const configList = [configUmid, configTemp]

        var hostname = "broker.hivemq.com";
        var port = 8000;
        var clientId = "dsakjdhskajdhsakjdSamir";
        clientId += new Date().getUTCMilliseconds();


        mqttClient = new Paho.MQTT.Client(hostname, port, clientId);
        mqttClient.onMessageArrived =  MessageArrived;
        mqttClient.onConnectionLost = ConnectionLost;
        Connect();
        function Connect(){
          mqttClient.connect({
            onSuccess: Connected,
            onFailure: ConnectionFailed,
            keepAliveInterval: 10,
          });
        }

        /*Callback for successful MQTT connection */
        function Connected() {
          console.log("Connected");
          mqttClient.subscribe("JjQZFhodDDghISIALBYQNS8/SCO");
        }

        /*Callback for failed connection*/
        function ConnectionFailed(res) {
          console.log("Connect failed:" + res.errorMessage);
        }

        /*Callback for lost connection*/
        function ConnectionLost(res) {
          if (res.errorCode != 0) {
          console.log("Connection lost:" + res.errorMessage);
          Connect();
          }
        }

        var listCounter = 30;
        setInterval(function () {
            listCounter++;
        }, 1000);

        /*Callback for incoming message processing */
        function MessageArrived(message) {
          if (listCounter < 30) { return ; }

          var date = new Date()
          writeChart(message.payloadString, date);

          listCounter = 0;
          tempChart.update(), umidChart.update();
        }

        function writeChart(msg, date) {
          var data = msg.split("$");
          data[2] = new Date(data[2] * 1000).toLocaleString("pt-BR", {timeZone: "America/Sao_Paulo"})
          console.log("data: ", data);

          for(let i=0 ; i<2 ; i++) {
            configList[i].data.labels.push(`${data[2].split(" ")[1]}`); // date.getHours()}:${date.getMinutes()
            configList[i].data.datasets[0].data.push(data[i]);

            if(configList[i].data.datasets[0].data.length > 20) {
              configList[i].data.datasets[0].data.shift()
              configList[i].data.labels.shift()
            }
          }
        }
    });
  </script>

</body>
</html>