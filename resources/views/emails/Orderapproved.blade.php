<style>
body{
    margin: 0;
    padding: 0;
}
.container{
    width: 100dvw;
    background: #f9f9f9;
    display: flex;
    flex-direction: row;
    align-items: center;
    place-items: center;
    justify-content: center;
}
.message-box{
    width: 85%;
    border-radius: 20px;
    background: #fff;
    box-sizing: border-box;
    padding: 15px;
    height: auto;
}
.item-container{
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
}
.total-box{
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    border-top: 3px solid black;
    border-bottom: 3px solid black;
    margin-top: 10px;
}
.text-center{
    text-align: center;
}
.fw-bold{
    font-weight: bolder;
}
.details-item{
    flex-direction: row;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.text-end{
    text-align: end;
}
.share{
    width: 100%;
    padding: 10rem;
}
.border-top{
    border-top: 1px solid #5555;
}
.border-bottom{
    border-bottom: 1px solid #555;
}
.pb-5{
    margin-bottom: 1em;
}
.mt-5{
    margin-block: 1rem;
}
.tracking-area{
    background-color: #e5eff9;
    padding-inline: .5rem;
    padding-block: 1rem;
    margin-top: 2rem;
}
.track-now{
    border: none;
    padding-inline: 1rem;
    padding-block: 1rem;
    width: 100%;
    flex-direction: row;
    align-self: center;
    justify-content: center;
    display: flex;
    background: #198fde;
    color: #fafafa;
}
.logo{
    width: 100px;
    height: 65px;
}
.logo-area{
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
}
.color-grey{
    color: #888888;
    font-weight: bolder;
}
.text-end{
    text-align: end;
}
</style>
<body>
    <div class="container">
        <div class="message-box">
            <h4>#{{ $id }}</h4>
            <div class="logo-area">
                <img src="./logo/logo.png" alt="" class="logo">
            </div>
            <h2 class="text-center fw-bold">Your Order Has Been Approved</h2>
            <h5 class="text-center fw-bold">Thanks For trusting us</h5>
            <div class="item-container">
                <p class="product-name fw-bold">Delivery Fee</p>
                <p class="product-price fw-bold">0$</p>
            </div>
            <div class="item-container">
                <p class="product-name fw-bold">Hidden Fee</p>
                <p class="product-price fw-bold">0$</p>
            </div>
            <div class="item-container">
                <p class="product-name fw-bold">Sub Total</p>
                <p class="product-price fw-bold">0$</p>
            </div>
            <div class="item-container">
                <p class="product-name fw-bold">Total</p>
                <p class="product-price fw-bold">{{ $total_price }}$</p>
            </div>
             <hr>
            <h1 class="text-start">Your Details</h1>
            <div class="details-item">
                <p>Shipping :</p>
                <div class="text-end">
                    <p>{{ $address }}</p>
                    <p>{{ $house_number }}</p>
                </div>
            </div>
            <div class="details-item border-top">
                <p>Billed To:</p>
                <div class="text-end">
                    <p>{{ $username }}</p>
                    <p>{{ $email  }}</p>
                </div>
            </div>
            <div class="details-item border-top">
                <p>Tracking Number</p>
                <p class="text-end">
                    {{ $trackingNumber }}
                </p>
            </div>
            <div class="mt-5">
                Track Your Package
            </div>
            <div class="tracking-area pb-5">
                <p>Click the link below to track your package and stay updated on its status.
                </p>
                <button class="track-now">Track Now</button>
            </div>
            <hr>
            <div class="text-center">
                <div class="d-block">
                    <p class="word-center">@2024 Get high. All rights Reserved</p>
                <p class="word-center">Gethigh.com</p>
                <p class="word-center">394-843-232-999</p>
                </div>
            </div>
         </div>
    </div>
</div>
</body>