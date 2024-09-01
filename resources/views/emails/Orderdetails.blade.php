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
.order-details{
    background-color: #c4f1c1;
    padding-inline: .5rem;
    padding-block: 1rem;
    margin-top: 2rem;
}
.details{
    border: none;
    padding-inline: 1rem;
    padding-block: 1rem;
    width: 50%;
    flex-direction: row;
    align-self: center;
    justify-content: center;
    display: flex;
    background: #22761f;
    color: #fafafa;
    border-radius: 10px;
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
.d-block{
    display: block;
}
.my-0{
    margin: 0px;
}
</style>
<body>
    <div class="container">
            <div class="message-box">
                <div class="logo-area">
                    <img src="./logo/logo.png" alt="" class="logo">
                </div>
                <h2 class="text-center fw-bold">THANKS FOR YOUR BUSINESS</h2>
                <h2 class="text-center fw-bold">Your Order</h2>
                <p class="text-center">Monday, Dec 2022 at 4:13pm</p>
                @foreach ($selections as $selection )    
                <div class="item-container">
                    <div class="d-block">
                        <p class="product-name my-0 fw-bold">{{ $selections->name }}</p>
                        <p class="product-name my-0 fw-bold">{{ $selections->quantity }} OZ</p>
                    </div>
                    <p class="product-price fw-bold">{{ $selections->price }}$</p>
                </div>
                @endforeach
                <div class="item-container">
                    <p class="product-name color-grey">Total Weight</p>
                    <p class="product-price fw-bold color-grey">{{ $quantity }} Oz</p>
                </div>
                <div class="item-container">
                    <p class="product-name color-grey">Total Packs</p>
                    <p class="product-price fw-bold color-grey">20 </p>
                </div>
                <div class="total-box">
                    <h4>Total</h4>
                    <h4>{{ $total_price }}$</h4>
                </div>
                <div class="mt-5">
                    Having Any problems with your order?
                </div>
                <div class="tracking-area pb-5">
                    <p>If you're having issues with your recent order, 
                        please click the button below to reach out to our support team. 
                        We'll be happy to help resolve any problems you may be experiencing.
                    </p>
                    <button class="track-now">Click Here to contact us</button>
                </div>
                <div class="mt-5">
                </div>
                <div class="order-details pb-5">
                    View Order Details in my Account
                    <p>Click Here to continue</p>
                    <button class="details">View Order Details</button>
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
</body>