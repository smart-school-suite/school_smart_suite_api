<style>
    body{
    margin: 0;
    padding: 0;
}
.logo{
    width: 100px;
    height: 100px;
    object-fit: contain;
}
.container{
    display: flex;
    flex-direction: row;
    width: 100dvw;
    background-color: #f9f9f9;
    height: 100dvh;
    place-items: center;
    justify-content: center;
    align-items: center;
}
.box-container{
    flex-direction: column;
    width: 90%;
    padding: 10px;
    border-radius: 10px;
}
.logo-area{
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
}
.message-box{
    width: 100%;
    background: #fff;
    border-radius: 10px;
    box-sizing: border-box;
    padding: 1rem;
}
.icon-box{
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    margin-block: 1rem;
}
.simple-line-icons--check {
    display: inline-block;
    width: 6em;
    height: 6em;
    --svg: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1024 1024'%3E%3Cpath fill='%23000' d='M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0m0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01m204.336-636.352L415.935 626.944l-135.28-135.28c-12.496-12.496-32.752-12.496-45.264 0c-12.496 12.496-12.496 32.752 0 45.248l158.384 158.4c12.496 12.48 32.752 12.48 45.264 0c1.44-1.44 2.673-3.009 3.793-4.64l318.784-320.753c12.48-12.496 12.48-32.752 0-45.263c-12.512-12.496-32.768-12.496-45.28 0'/%3E%3C/svg%3E");
    background-color: #5cb85a;
    -webkit-mask-image: var(--svg);
    mask-image: var(--svg);
    -webkit-mask-repeat: no-repeat;
    mask-repeat: no-repeat;
    -webkit-mask-size: 100% 100%;
    mask-size: 100% 100%;
  }
.text-center{
    text-align: center;
}
.fs-6{
   font-size: 1.3rem;
}
h3{
    font-size: 1.4rem;
    font-weight: bolder;

}
.footer{
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}
.text-center{
   text-align: center; 
}
.action-area{
    width: 90%;
    padding-block: 1rem;
    padding-inline: 1rem;
    background: #e5eff9;
    margin-top: 5rem;
}
.order-btn{
    border: none;
    background: #236ea6;
    color: #fff;
    width: 100%;
    box-sizing: border-box;
    padding: 1.5rem;
}
</style>
<body>
    <div class="container">
        <div class="box-container">
            <div class="logo-area">
                <img src="./logo/logo.png" alt="" class="logo">
            </div>
            <div class="message-box">
                <div class="icon-box">
                    <span class="simple-line-icons--check"></span>
                </div>
                <h1 class="text-center">Transaction Completed</h1>
                <h3>Hi, {{ $username }} </h3>
                <p></p>
                <div class="fs-6">
                    We are pleased to inform you that we have successfully received the payment of ${{$total_price}} from your account. 
                    This payment confirms your commitment to our agreement, and we are grateful for your trust in our services.
                     Our team is now working diligently to provide the agreed-upon services and deliver exceptional results. 
                     We will keep you updated on our progress and look forward to continuing our collaboration. 
                     If you have any questions or concerns, please do not hesitate to reach out to us.
                </div>
                <hr>
                 <p>
                    Following this Payment we are thrilled to say your order has been approved
                 </p>
                <div class="action-area">
                    <p>Click The button below to chech your order details</p>
                    <button class="order-btn">Check Order</button>
                </div>
            </div>
            <div class="footer">
                <div class="d-block">
                    <p class="text-center">@2024 Get high. All rights Reserved</p>
                <p class="text-center">Gethigh.com</p>
                <p class="text-center">394-843-232-999</p>
                </div>
            </div>
        </div>
    </div>
</body>