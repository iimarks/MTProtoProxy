# WeCan MTProtoProxy
MTProxy For Shared Host / Linux Server

1. upload files on your host
2. run **WeMTProxy.php** file in browser.
3. generate secret key or use default secret key
4. fill last form:
  *port:* opened port on your host like 8080
  *secert:* generated secret key 
  *tag:* tag for promotion channel (get from @MTProxybot)  ==> not work yet
5. Run Proxy
6. set cron job:
```* * * * * php /home/user/WeMTProxy/WeMTProxy.php <PORT> <SECRET> <TAG>```
   
- your host should support exe and pcnt functions.
- if not runned for you, see php-error.log file.
- you can use it from server:
  ```# php WeMTProxy.php <PORT> <SECRET> <TAG>```
  
  
WeCan-Co.ir
@WeCanCo
@WeCanGP
