<style>
    body{
    padding: 0;
    margin: 0;
}
.container{
    height: 100dvh;
    width: 100dvw;
    background-color: #f9f9f9;
    display: flex;
    flex-direction: row;
    place-items: center;
    justify-content: center;
    align-items: center;
}
.message-box{
    width: 85%;
    border-radius: 20px;
    background-color: #fff;
    height: auto;
    max-height: 100dvh;
    box-sizing: border-box;
    padding: 10px;
}
.logo{
    width: 100px;
    height: 70px;
}
.logo-box{
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    margin-top: 2rem;
}
h1{
    font-size: 1.5rem;
    font-weight: 900;
    text-align: center;
}
.title-box{
    flex-direction: row;
    display: flex;
    align-items: center;
}
p{
    font-size: 1.2rem;
}
.w-100{
    border-bottom: .5px solid #ccc;
    width: 100%;
    padding-bottom: 1rem;
}
.user-text{
    font-size: 1.1rem;
    font-weight: 900;
}
.mdi--hand-wave {
    display: inline-block;
    width: 1em;
    height: 1em;
    --svg: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23000' d='M23 17c0 3.31-2.69 6-6 6v-1.5c2.5 0 4.5-2 4.5-4.5zM1 7c0-3.31 2.69-6 6-6v1.5c-2.5 0-4.5 2-4.5 4.5zm7-2.68l-4.59 4.6c-3.22 3.22-3.22 8.45 0 11.67s8.45 3.22 11.67 0l7.07-7.09c.49-.47.49-1.26 0-1.75a1.25 1.25 0 0 0-1.77 0l-4.42 4.42l-.71-.71l6.54-6.54c.49-.49.49-1.28 0-1.77s-1.29-.49-1.79 0L14.19 13l-.69-.73l6.87-6.89c.49-.49.49-1.28 0-1.77s-1.28-.49-1.77 0l-6.89 6.89l-.71-.7l5.5-5.48c.5-.49.5-1.28 0-1.77s-1.28-.49-1.77 0l-7.62 7.62a4 4 0 0 1-.33 5.28l-.71-.71a3 3 0 0 0 0-4.24l-.35-.35l4.07-4.07c.49-.49.49-1.28 0-1.77c-.5-.48-1.29-.48-1.79.01'/%3E%3C/svg%3E");
    background-color: orange;
    -webkit-mask-image: var(--svg);
    mask-image: var(--svg);
    -webkit-mask-repeat: no-repeat;
    mask-repeat: no-repeat;
    -webkit-mask-size: 100% 100%;
    mask-size: 100% 100%;
  }
  .btn-success{
    width: 90%;
    border-radius: 5px;
    padding: 0.8rem;
    margin-top: 0.6rem;
    border: none;
    font-size: 15px;
    font-weight: 500;
    background-color: steelblue;
    color: #fafafa;
  }
  .text-center{
    flex-direction: row;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    margin-top: 1.5rem;
  }
  .word-center{
    text-align: center;
    font-size: 10px;
  }
  .d-block{
    display: block;
  }
  
</style>
<body>
    <div class="container">
        <div class="message-box">
           <div class="logo-box">
               <img src="./logo/logo.png" alt="" class="logo">
           </div>
           <h2>{{ $title }}</h2>
           <div class="title-box">
               <img src="" alt="">
               <p class="user-text">Hello, {{ $username }}! <span class="mdi--hand-wave"></span></p>
           </div>
           <div class="message-body">
              <p>{{ $text }}</p>
           </div>
           <div class="w-100">
               <button class="btn-success">Click Here to complete transaction</button>
           </div>
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