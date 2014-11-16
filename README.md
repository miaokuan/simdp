#simdp
a simple dispatcher
一个简单的任务依赖检测调度工具，只涉及依赖检测和调度通知；
实际任务由外部执行（callback），完成后通知调度系统标示为完成。

###bin/simdp rely
daemon模式任务依赖检测
ex:nohup bin/simdp rely > /dev/null 2>&1 &

###bin/simdp task
daemon模式任务通知分发，建议接收到通知后异步返回，真正完成后调用接口通知完成。 bin/simdp task finish "job_id=1&time=20141116110000"
ex:nohup bin/simdp task > /dev/null 2>&1 &

###bin/simdp job add "params"
添加job, 返回job_id
必选params: name,freq,callback(json)
可选params: priority,userid
ex:bin/simdp job add "name=第一个job&freq=3600&callback={'protocol':'sh','val':'ls'}"

###bin/simdp job del "params"
删除job
必选params: job_id
ex:bin/simdp job del "job_id=1"

###bin/simdp rely add "params"
为job添加依赖
必选params: job_id,rely_job,start,long
ex:bin/simdp rely add "job_id=2&rely_job=1&start=0&long=1"

###bin/simdp rely del "params"
为job删除依赖
必须params:job_id,rely_job
ex:bin/simdp rely del "job_id=2&rely_job=1"

###bin/simdp task add "params"
任务添加
必选params: job_id,time
ex:bin/simdp task add "job_id=1&time=201411161200"

###bin/simdp task del "params"
任务删除
必选params: job_id,time
ex:bin/simdp task del "job_id=1&time=201411161200"

###bin/simdp task finish|pending|ready|kill
变更任务状态
必选params: job_id,time
ex:bin/simdp task finsh "job_id=1&time=201411161200"
