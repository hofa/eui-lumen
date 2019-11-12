<template>
  <div class="login-container">
    <el-row>
      <el-col :span="4">
        <ul>
          <li v-for="(data, i) in customerList" :key="i" @click="handleCustomerChange(data)">
            <el-button :type="data.uuid == touuid ? '': 'info' "> {{ data.nickname }} </el-button>
          </li>
        </ul>
      </el-col>
      <el-col :span="20">
        <el-form v-show="touuid" class="login-form" auto-complete="on" label-position="left">

          <div class="title-container">
            <h3 class="title">聊天</h3>
          </div>
          <ul id="wsd" ref="wsd" class="infinite-list" style="overflow:auto;height:500px;width:100%">
            <li v-for="(data, i) in contents" :key="i" class="infinite-list-item">
              <small v-if="data.from_client_id == bindForm.client_id">me</small>
              <small v-else>{{ data.nickname }}</small>
              <el-button :type="data.uuid == uuid ? 'success' : 'danger' "> {{ data.content }} </el-button>
            </li>
          </ul>
          <el-form-item prop="message" :error="form.errors.has('message') ? form.errors.get('message') : ''">
            <el-input
              ref="message"
              v-model="form.message"
              placeholder="message"
              name="message"
              type="text"
              tabindex="1"
              auto-complete="on"
            />
          </el-form-item>
          <el-button :loading="loading" type="primary" style="width:100%;margin-bottom:30px;" @click.native.prevent="handleSend">发送</el-button>

        </el-form>
      </el-col>
    </el-row>

  </div>
</template>

<script>
import Form from '@/utils/form'

export default {
  name: 'Server',
  data() {
    return {
      form: new Form({
        message: ''
      }),
      bindForm: new Form({
        client_id: ''
      }),
      customerForm: new Form({

      }),
      historyForm: new Form({
        // room_id: ''
      }),
      roomForm: new Form({

      }),
      customerList: [],
      loading: false,
      contents: [],
      websock: null,
      touuid: '',
      uuid: ''
    }
  },
  created() {
    window.document.title = '客服服务'
    this.initWebSocket()
    this.handleCustomer()
  },
  destroyed() {
    this.websock.close() // 离开路由之后断开websocket连接
  },
  methods: {

    handleSend() {
      this.loading = true
      // this.websock.send(this.form.message)
      // this.form.message = ''
      // this.loading = false
      this.form.post('/websocket/send/' + this.touuid).then(() => {
        this.form.message = ''
        this.loading = false
        // this.$ref.wsd.scrollTop = this.$ref.wsd.scrollHeight
        // this.$nextTick(function() {
        //   var container = this.$el.querySelector('#wsd')
        //   container.scrollTop = container.scrollHeight
        // })
      }).catch(() => {
        this.loading = false
      })
    },
    handleCustomer() {
      this.customerForm.get('/websocket/customer').then(({ data }) => {
        this.customerList = data.data
        // console.log(data.data)
      }).catch(() => {

      })
    },
    handleCustomerChange(row) {
      // console.log('handleCustomerChange', row)
      this.touuid = row.uuid
      // this.historyForm.room_id = row.uuid
      this.historyForm.get('/websocket/history/' + row.uuid).then(({ data }) => {
        this.contents = data.data.reverse().concat(this.contents)
        this.$nextTick(function() {
          var container = this.$el.querySelector('#wsd')
          container.scrollTop = container.scrollHeight
        })

        this.roomForm.post('/websocket/room/' + row.uuid).then(({ data }) => {

        }).catch(() => {

        })
      }).catch(() => {

      })
    },
    initWebSocket() { // 初始化weosocket
      const wsuri = 'ws://127.0.0.1:8282'
      this.websock = new WebSocket(wsuri)
      this.websock.onmessage = this.websocketonmessage
      this.websock.onopen = this.websocketonopen
      this.websock.onerror = this.websocketonerror
      this.websock.onclose = this.websocketclose
    },
    websocketonopen() { // 连接建立之后执行send方法发送数据
      // const actions = { 'test': '12345' }
      // this.websocketsend(JSON.stringify(actions))
    },
    websocketonerror() { // 连接建立失败重连
      this.initWebSocket()
    },
    websocketonmessage(e) { // 数据接收
      const redata = JSON.parse(e.data)
      switch (redata['type']) {
        case 'bind':
          this.bindForm.client_id = redata['client_id']
          this.bindForm.post('/websocket/bind').then(({ data }) => {
            this.uuid = data.data.uuid
          }).catch(() => {

          })
          break
        case 'ping':
          this.websock.send('pong')
          break
        case 'message':
          this.contents.push(redata)
          this.$nextTick(function() {
            var container = this.$el.querySelector('#wsd')
            container.scrollTop = container.scrollHeight
          })
          break
        case 'online':
          this.$notify({
            title: '上线通知',
            message: '上线',
            type: 'success',
            duration: 2000
          })
          break
      }
      // console.log(e.data)
    },
    websocketsend(Data) { // 数据发送
      this.websock.send(Data)
    },
    websocketclose(e) { // 关闭
      console.log('断开连接', e)
    }
  }
}
</script>

<style lang="scss">
/* 修复input 背景不协调 和光标变色 */
/* Detail see https://github.com/PanJiaChen/vue-element-admin/pull/927 */

$bg:#283443;
$light_gray:#fff;
$cursor: #fff;

@supports (-webkit-mask: none) and (not (cater-color: $cursor)) {
  .login-container .el-input input {
    color: $cursor;
  }
}

/* reset element-ui css */
.login-container {
  .el-input {
    display: inline-block;
    height: 47px;
    width: 85%;

    input {
      background: transparent;
      border: 0px;
      -webkit-appearance: none;
      border-radius: 0px;
      padding: 12px 5px 12px 15px;
      color: $light_gray;
      height: 47px;
      caret-color: $cursor;

      &:-webkit-autofill {
        box-shadow: 0 0 0px 1000px $bg inset !important;
        -webkit-text-fill-color: $cursor !important;
      }
    }
  }

  .el-form-item {
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(0, 0, 0, 0.1);
    border-radius: 5px;
    color: #454545;
  }
}
</style>

<style lang="scss" scoped>
$bg:#2d3a4b;
$dark_gray:#889aa4;
$light_gray:#eee;

.login-container {
  min-height: 100%;
  width: 100%;
  background-color: $bg;
  overflow: hidden;

  .login-form {
    position: relative;
    width: 100%;
    max-width: 100%;
    padding: 160px 35px 0;
    margin: 0 auto;
    overflow: hidden;
  }

  .tips {
    font-size: 14px;
    color: #fff;
    margin-bottom: 10px;

    span {
      &:first-of-type {
        margin-right: 16px;
      }
    }
  }

  .svg-container {
    padding: 6px 5px 6px 15px;
    color: $dark_gray;
    vertical-align: middle;
    width: 30px;
    display: inline-block;
  }

  .title-container {
    position: relative;

    .title {
      font-size: 26px;
      color: $light_gray;
      margin: 0px auto 40px auto;
      text-align: center;
      font-weight: bold;
    }
  }

  .show-pwd {
    position: absolute;
    right: 10px;
    top: 7px;
    font-size: 16px;
    color: $dark_gray;
    cursor: pointer;
    user-select: none;
  }
}

.infinite-list .infinite-list-item {
    display: flex;
    align-items: center;
    justify-content: left;
    height: 50px;
    // background: #889aa4;
    margin-top: 10px;
    color: #fff;
}
ul {
    padding:0;
    // margin:0;
}
</style>
