const express = require('express');
const app = express();
const path = require('path');
//リクエストを記録
const loggerMiddleware = (req,res,next)=>{
    console.log(`${new Date()}: ${req.method} ${req.url}`)
    next();
}

app.use(loggerMiddleware);
app.use(express.static('public', { maxAge: 86400000 })); //キャッシュ時間に注意

// test-imageリクエスト


// GETリクエスト
app.get('/', (req, res) => {

  });
  
  // POSTリクエスト
  app.post('/', (req, res) => {
    res.send({
      msg:'POST request'
    });
  });
  
  // PUTリクエスト
  app.put('/:id', (req, res) => {
    res.send({
      id: req.params.id,
      msg:'PUT request'
    });
  });
  
  // DELETEリクエスト
  app.delete('/:id', (req, res) => {
    res.send({
      id: req.params.id,
      msg:'DELETE request'
    });
  });

app.get('/', (req, res) => {
  res.send('Hello World!');
});

app.listen(3000, () => {
  console.log('Server listening on port 3000');
});