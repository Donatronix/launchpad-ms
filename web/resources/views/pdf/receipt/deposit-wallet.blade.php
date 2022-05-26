<html>
<head>
    <title></title>
    <link rel="stylesheet" href="./bootstrap-4.6.1.min.css">
    <style type="text/css">
        .content-cover{
            padding: 20px;
            border: 1px solid #E6E8EC;
            border-radius: 5px;

        }
        .block{
            background: linear-gradient(0deg, rgba(241, 245, 250, 0.5), rgba(241, 245, 250, 0.5)), #F1F5FA;
            padding: 10px 100px;
            width: 70%;
            text-align: center;
        }
        .transaction{
            padding-right: 100px;
            margin-left: 20px;
        }
        .btn-pink{
            background-color: #FF6838;
            float: right;
            width: 279px;
        }
        .download{
            width: 70%;
            padding-top: 40px;
        }
        .margin-middle {
            margin: 0 auto;
        }
        .btn-blue{
            background-color: #666DFF;
        }
        .amount{ color: #58BD7D;}
        .wrapper{padding-top: 40px;}
        .span-label{color: #777E91}
    </style>
</head>
<body>
<div class="wrapper">
    <div class="col-md-4 margin-middle" style="text-align: center;">
        <h2><b>Congrats!</b></h2>
        <p>You successfully paid a deposit of <span class="amount">${{$total_amount}}</span></p>
        <button class="btn btn-primary rounded-pill btn-blue">View Dashboard</button>
    </div>
    <div class="col-md-8 margin-middle" style="padding-top: 40px;">
        <div class="content-cover">
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <span class="span-label">Card Number</span>
                    <span style="float: right;">{{$card_number}}</span>
                </li>
                <li class="list-group-item">
                    <span class="span-label">Credit card type</span>
                    <span style="float: right;">{{$credit_card_type['label']}}</span>
                </li>
                <li class="list-group-item">
                    <span class="span-label">Total amount</span>
                    <span style="float: right;">{{$total_amount}}</span>
                </li>
                <li class="list-group-item">
                    <span class="span-label">Time</span>
                    <span style="float: right;">January</span>
                </li>
                <li class="list-group-item">
                    <span class="span-label">Paid invoice confirmation for</span>
                    <span style="float: right;">{{$user['first_name']}} {{$user['last_name']}}</span>
                </li>
                <li class="list-group-item">
                    <span class="span-label">Invoice</span>
                    <span style="float: right;">{{$order['id']}}</span>
                </li>
            </ul>
            <div>
                <span class="transaction">Transaction ID</span>
                <span class="block">{{$id}}</span>
            </div>
        </div>
        <div class="download">
            <button class="btn btn-primary rounded-pill btn-pink">Download Reciept PDF</button>
        </div>
    </div>
</div>
</body>
</html>
