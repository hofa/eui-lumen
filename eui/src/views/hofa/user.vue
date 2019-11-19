<template>
  <div class="app-container">
    <div class="filter-container">
      <el-select
        v-model="searchForm.field"
        placeholder="条件"
        filterable
        clearable
        style="width: 90px"
        class="filter-item"
      >
        <el-option v-for="(item, index) in userSearchFieldOption" :key="index" :label="item" :value="index" />
      </el-select>
      <el-input
        v-model="searchForm.val"
        placeholder="值"
        style="width: 200px;"
        class="filter-item"
        @keyup.enter.native="handleFilter"
      />
      <el-select
        v-model="searchForm.status"
        placeholder="状态"
        clearable
        style="width: 90px"
        class="filter-item"
      >
        <el-option v-for="(item, index) in statusOption" :key="index" :label="item" :value="index" />
      </el-select>
      <el-select
        v-model="searchForm.role"
        placeholder="角色"
        filterable
        clearable
        style="width: 90px"
        class="filter-item"
      >
        <el-option v-for="(item, index) in roleOption" :key="index" :label="item" :value="index" />
      </el-select>
      <el-date-picker
        v-model="searchForm.created_at"
        type="daterange"
        align="right"
        unlink-panels
        range-separator="-"
        start-placeholder="创建日期"
        end-placeholder="结束日期"
        :picker-options="pickerOptions"
      />
      <el-button
        v-waves
        :disabled="btns.search"
        class="filter-item"
        type="primary"
        icon="el-icon-search"
        @click="handleFilter"
      >搜索</el-button>
      <el-button
        class="filter-item"
        :disabled="btns.add"
        style="margin-left: 10px;"
        type="primary"
        icon="el-icon-edit"
        @click="handleCreate"
      >新增</el-button>
      <el-button
        v-waves
        :disabled="btns.export"
        :loading="downloadLoading"
        class="filter-item"
        type="primary"
        icon="el-icon-download"
        @click="handleDownload"
      >导出</el-button>
    </div>

    <el-divider />

    <el-table
      :key="tableKey"
      v-loading="listLoading"
      :data="list"
      border
      fit
      highlight-current-row
      style="width: 100%;"
      @sort-change="sortChange"
    >
      <!-- <el-table-column
        label="ID"
        prop="id"
        sortable="custom"
        align="center"
        width="120"
        :class-name="getSortClass('id')"
      >
        <template slot-scope="scope">
          <span>{{ scope.row.id }}</span>
        </template>
      </el-table-column> -->
      <el-table-column label="账号" width="150px" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.username }}</span>
        </template>
      </el-table-column>
      <el-table-column label="创建时间" min-width="200px" align="center" prop="created_at" sortable="custom" :class-name="getSortClass('created_at')">
        <template slot-scope="scope">
          <span>{{ scope.row.created_at }}</span>
        </template>
      </el-table-column>
      <el-table-column label="角色" width="120px">
        <template slot-scope="{row}">
          <el-tag v-for="item in row.role" :key="item">{{ roleOption[item] }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="状态" width="80px">
        <template slot-scope="{row}">
          <el-tag :type="row.status | statusFilter">{{ statusOption[row.status] }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="操作" width="200px" align="center">
        <template slot-scope="{row}">
          <el-button type="primary" size="mini" :disabled="btns.edit" @click="handleUpdate(row)">编辑</el-button>
          <el-button type="primary" size="mini" :disabled="btns.info" @click="handleInfoUpdate(row)">个人信息</el-button>
        </template>
      </el-table-column>
    </el-table>

    <pagination
      v-show="total>0"
      :total="total"
      :page.sync="listQuery.page"
      :limit.sync="listQuery.limit"
      @pagination="getList"
    />

    <el-dialog :title="textMap[dialogStatus]" :visible.sync="dialogFormVisible">
      <el-form
        ref="dataForm"
        label-position="left"
        label-width="70px"
        style="width: 400px; margin-left:50px;"
      >
        <el-form-item
          label="账号"
          prop="username"
          :error="form.errors.has('username') ? form.errors.get('username') : ''"
        >
          <el-input v-model="form.username" placeholder="账号" :disabled="dialogStatus == 'update'" />
        </el-form-item>

        <el-form-item
          label="密码"
          prop="password"
          :error="form.errors.has('password') ? form.errors.get('password') : ''"
        >
          <el-input v-model="form.password" placeholder="密码" />
        </el-form-item>

        <el-form-item
          label="角色"
          prop="role"
          :error="form.errors.has('role') ? form.errors.get('role') : ''"
        >
          <div style="height: 200px;">
            <el-scrollbar style="height:100%">
              <el-checkbox
                v-model="checkAll"
                :indeterminate="isIndeterminate"
                @change="handleCheckAllChange"
              >全选</el-checkbox>
              <div style="margin: 15px 0;" />
              <el-checkbox-group v-model="form.role" @change="handleCheckedRoleChange">
                <el-checkbox
                  v-for="(item, index) in roleOption"
                  :key="index"
                  :label="index"
                  :value="index"
                >{{ item }}</el-checkbox>
              </el-checkbox-group>
            </el-scrollbar>
          </div>
        </el-form-item>

        <el-form-item
          label="状态"
          prop="status"
          :error="form.errors.has('status') ? form.errors.get('status') : ''"
        >
          <el-select v-model="form.status" class="filter-item" placeholder="请选择">
            <el-option
              v-for="(item, index) in statusOption"
              :key="index"
              :label="item"
              :value="index"
            />
          </el-select>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogFormVisible = false">取消</el-button>
        <el-button
          type="primary"
          :loading="submitLoading"
          @click="dialogStatus==='create'?createData():updateData()"
        >确定</el-button>
      </div>
    </el-dialog>

    <el-dialog title="个人信息" :visible.sync="dialogInfoFormVisible">

      <el-col :span="12">
        <el-form
          ref="dataInfoForm"
          label-position="left"
          label-width="90px"
          style="width: 250px; margin-left:20px;"
        >

          <el-form-item
            label="QQ"
            prop="qq"
            :error="infoForm.errors.has('qq') ? infoForm.errors.get('qq') : ''"
          >
            <el-input v-model="infoForm.qq" placeholder="QQ号" />
          </el-form-item>

          <el-form-item
            label="微信"
            prop="weixin"
            :error="infoForm.errors.has('weixin') ? infoForm.errors.get('weixin') : ''"
          >
            <el-input v-model="infoForm.weixin" placeholder="微信号" />
          </el-form-item>

          <el-form-item
            label="手机区号"
            prop="mobile_area"
            :error="infoForm.errors.has('mobile_area') ? infoForm.errors.get('mobile_area') : ''"
          >
            <el-input v-model="infoForm.mobile_area" placeholder="手机区号" />
          </el-form-item>
          <el-form-item
            label="手机"
            prop="mobile"
            :error="infoForm.errors.has('mobile') ? infoForm.errors.get('mobile') : ''"
          >
            <el-input v-model="infoForm.mobile" placeholder="手机号码" />
          </el-form-item>
          <el-form-item
            label="邮箱"
            prop="email"
            :error="infoForm.errors.has('email') ? infoForm.errors.get('email') : ''"
          >
            <el-input v-model="infoForm.email" placeholder="邮箱" />
          </el-form-item>
          <el-form-item
            label="头像"
            prop="avatar"
            :error="infoForm.errors.has('avatar') ? infoForm.errors.get('avatar') : ''"
          >
            <!-- <el-input v-model="infoForm.avatar" placeholder="头像" /> -->
            <el-upload
              class="avatar-uploader"
              :headers="uploadHeader"
              :action="uploadAction"
              :show-file-list="false"
              :on-success="handleAvatarSuccess"
              :before-upload="beforeAvatarUpload"
            >
              <img v-if="infoForm.avatar" :src="infoForm.avatar" class="avatar">
              <i v-else class="el-icon-plus avatar-uploader-icon" />
            </el-upload>
          </el-form-item>
          <el-form-item
            label="性别"
            prop="sex"
            :error="infoForm.errors.has('sex') ? infoForm.errors.get('sex') : ''"
          >
            <el-select v-model="infoForm.sex" class="filter-item" placeholder="请选择">
              <el-option
                v-for="(item, index) in sexOption"
                :key="index"
                :label="item"
                :value="index"
              />
            </el-select>
          </el-form-item>
        </el-form>
      </el-col>

      <el-col :span="12">
        <el-form
          ref="dataInfoForm2"
          label-position="left"
          label-width="100px"
          style="width: 280px;"
        >
          <el-form-item
            label="昵称"
            prop="nickname"
            :error="infoForm.errors.has('nickname') ? infoForm.errors.get('nickname') : ''"
          >
            <el-input v-model="infoForm.nickname" placeholder="昵称" />
          </el-form-item>
          <el-form-item
            label="真实姓名"
            prop="real_name"
            :error="infoForm.errors.has('real_name') ? infoForm.errors.get('real_name') : ''"
          >
            <el-input v-model="infoForm.real_name" placeholder="真实姓名" />
          </el-form-item>
          <el-form-item
            label="身份证号码"
            prop="idcard"
            :error="infoForm.errors.has('idcard') ? infoForm.errors.get('idcard') : ''"
          >
            <el-input v-model="infoForm.idcard" placeholder="身份证号码" />
          </el-form-item>
          <el-form-item
            label="余额"
            prop="wallet"

            :error="infoForm.errors.has('wallet') ? infoForm.errors.get('wallet') : ''"
          >
            <el-input v-model="infoForm.wallet" placeholder="余额" :disabled="hide" />
          </el-form-item>
          <el-form-item
            label="注册IP"
            prop="register_ip"

            :error="infoForm.errors.has('register_ip') ? infoForm.errors.get('register_ip') : ''"
          >
            <el-input v-model="infoForm.register_ip" placeholder="注册IP" :disabled="hide" />
          </el-form-item>
          <el-form-item
            label="最后登录IP"
            prop="last_login_ip"

            :error="infoForm.errors.has('last_login_ip') ? infoForm.errors.get('last_login_ip') : ''"
          >
            <el-input v-model="infoForm.last_login_ip" placeholder="最后登录IP" :disabled="hide" />
          </el-form-item>
          <el-form-item
            label="最后登录时间"
            prop="last_login_time"

            :error="infoForm.errors.has('last_login_time') ? infoForm.errors.get('last_login_time') : ''"
          >
            <el-input v-model="infoForm.last_login_time" placeholder="最后登录IP" :disabled="hide" />
          </el-form-item>
        </el-form>
      </el-col>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogInfoFormVisible = false">取消</el-button>
        <el-button
          type="primary"
          :loading="submitLoading"
          @click="updateInfoData()"
        >确定</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<style>
.el-scrollbar__wrap {
  overflow-x: hidden;
}
.avatar-uploader .el-upload {
  border: 1px dashed #d9d9d9;
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
}
.avatar-uploader .el-upload:hover {
  border-color: #409EFF;
}
.avatar-uploader-icon {
  font-size: 28px;
  color: #8c939d;
  width: 38px;
  height: 38px;
  line-height: 38px;
  text-align: center;
}
.avatar {
  width: 38px;
  height: 38px;
  display: block;
}
</style>

<script>
import Form from '@/utils/form'
import waves from '@/directive/waves' // waves directive
import { parseTime, getObjectKeys, allow, disable } from '@/utils'
import { getToken } from '@/utils/auth'
import Pagination from '@/components/Pagination' // secondary package based on el-pagination
// import { mapGetters } from 'vuex'
export default {
  name: 'ComplexTable',
  components: { Pagination },
  directives: { waves },
  filters: {
    statusFilter(status) {
      const statusMap = {
        Normal: 'success',
        Close: 'danger'
      }
      return statusMap[status]
    }
  },
  data() {
    return {
      hide: true,
      btns: {
        add: true,
        edit: true,
        search: true,
        export: true,
        info: true
      },
      checkAll: false,
      isIndeterminate: true,
      tableKey: 0,
      pickerOptions: {
        shortcuts: [
          {
            text: '今天',
            onClick(picker) {
              const end = new Date()
              const start = new Date()
              picker.$emit('pick', [start, end])
            }
          },
          {
            text: '昨天',
            onClick(picker) {
              const end = new Date()
              const start = new Date()
              start.setTime(start.getTime() - 3600 * 1000 * 24)
              end.setTime(end.getTime() - 3600 * 1000 * 24)
              picker.$emit('pick', [start, end])
            }
          },
          {
            text: '最近一周',
            onClick(picker) {
              const end = new Date()
              const start = new Date()
              start.setTime(start.getTime() - 3600 * 1000 * 24 * 7)
              picker.$emit('pick', [start, end])
            }
          },
          {
            text: '最近一个月',
            onClick(picker) {
              const end = new Date()
              const start = new Date()
              start.setTime(start.getTime() - 3600 * 1000 * 24 * 30)
              picker.$emit('pick', [start, end])
            }
          },
          {
            text: '最近三个月',
            onClick(picker) {
              const end = new Date()
              const start = new Date()
              start.setTime(start.getTime() - 3600 * 1000 * 24 * 90)
              picker.$emit('pick', [start, end])
            }
          }
        ]
      },
      list: null,
      total: 0,
      listLoading: true,
      submitLoading: false,
      listQuery: {
        page: 1,
        limit: 20,
        sort: '-created_at'
      },
      dialogFormVisible: false,
      dialogInfoFormVisible: false,
      dialogStatus: '',
      textMap: {
        update: '编辑',
        create: '新增'
      },
      downloadLoading: false,
      searchForm: new Form({
        field: 'Username',
        val: '',
        status: '',
        created_at: '',
        role: ''
      }),
      form: new Form({
        id: 0,
        username: '',
        password: '',
        status: '',
        role: []
      }),
      infoSerachForm: new Form({

      }),
      infoForm: new Form({
        'qq': '',
        'weixin': '',
        'mobile': '',
        'mobile_area': '',
        'email': '',
        'avatar': '',
        'sex': '',
        'nickname': '',
        'real_name': '',
        'idcard': '',
        'from_user_id': '',
        'register_ip': '',
        'last_login_ip': '',
        'last_login_time': '',
        'wallet': '',
        'level_id': '',
        'channel_id': '',
        'user_id': ''
      }),
      optionForm: new Form({
        ins: 'status,role,sex,userSearchField'
      }),
      statusOption: [],
      roleOption: [],
      sexOption: [],
      userSearchFieldOption: [],
      uploadAction: process.env.VUE_APP_BASE_API + '/upload',
      uploadHeader: {
        Authorization: 'Bearer ' + getToken()
      }
    }
  },
  mounted() {
    const allowOpen = allow('M:/user/user')
    if (allowOpen) {
      this.btns.add = disable('N:Post:/user')
      this.btns.edit = disable('N:Put:/user/{id}')
      this.btns.search = disable('N:Get:/user')
      this.btns.export = disable('V:/user/export')
      this.btns.info = disable('N:Get:/user/info/{id}')
      const allowGetOption = allow('N:Get:/option')
      if (!this.btns.search) {
        this.getList()
      }

      if (allowGetOption) {
        this.getOptions()
      }
    } else {
      this.$router.push('/401')
    }
    // this.uploadActon = process.env.VUE_APP_BASE_API + '/upload'
  },
  methods: {
    handleCheckAllChange(val) {
      this.form.role = val ? getObjectKeys(this.roleOption) : []
      this.isIndeterminate = false
    },
    handleCheckedRoleChange(value) {
      const checkedCount = value.length
      this.checkAll = checkedCount === this.roleOption.length
      this.isIndeterminate =
        checkedCount > 0 && checkedCount < this.roleOption.length
    },
    getList() {
      this.listLoading = true
      this.searchForm
        .get(
          '/user?page=' +
            this.listQuery.page +
            '&psize=' +
            this.listQuery.limit +
            '&sort=' +
            this.listQuery.sort
        )
        .then(({ data }) => {
          this.list = data.data
          this.total = data.meta.total
          this.listLoading = false
        })
        .catch(() => {
          this.listLoading = false
        })
    },
    handleFilter() {
      this.listQuery.page = 1
      this.getList()
    },
    sortChange(data) {
      const { prop, order } = data
      if (prop === 'id') {
        this.sortByID(order)
      }
      if (prop === 'created_at') {
        this.sortByCreatedAt(order)
      }
    },
    sortByID(order) {
      if (order === 'ascending') {
        this.listQuery.sort = '*id'
      } else {
        this.listQuery.sort = '-id'
      }
      this.handleFilter()
    },
    sortByCreatedAt(order) {
      if (order === 'ascending') {
        this.listQuery.sort = '*created_at'
      } else {
        this.listQuery.sort = '-created_at'
      }
      this.handleFilter()
    },
    getOptions() {
      this.optionForm.get('/option').then(({ data }) => {
        this.statusOption = data.data.status
        this.roleOption = data.data.role
        this.sexOption = data.data.sex
        this.userSearchFieldOption = data.data.userSearchField
      })
    },
    handleCreate() {
      this.form.reset()
      this.form.status = 'Normal'
      this.dialogStatus = 'create'
      this.dialogFormVisible = true
    },
    createData() {
      this.submitLoading = true
      this.form
        .post('/user')
        .then(({ data }) => {
          this.dialogFormVisible = false
          this.$notify({
            title: 'Success',
            message: data.meta.message,
            type: 'success',
            duration: 2000
          })
          this.submitLoading = false
          this.getList()
        })
        .catch(() => {
          this.submitLoading = false
        })
    },
    handleUpdate(row) {
      row.role = row.role.map(function(data) {
        return data.toString()
      })
      this.form.clear()
      this.form.fill(row)
      this.dialogStatus = 'update'
      this.dialogFormVisible = true
    },
    handleInfoUpdate(row) {
      this.listLoading = true
      this.infoSerachForm.get('/user/info/' + row.id).then(({ data }) => {
        this.listLoading = false
        this.infoForm.fill(data.data)
        this.dialogInfoFormVisible = true
      }).catch(() => {
        this.listLoading = false
      })
    },
    updateData() {
      this.submitLoading = true
      this.form
        .put('/user/' + this.form.id)
        .then(({ data }) => {
          this.dialogFormVisible = false
          this.$notify({
            title: 'Success',
            message: data.meta.message,
            type: 'success',
            duration: 2000
          })
          this.submitLoading = false
          this.getList()
        })
        .catch(() => {
          this.submitLoading = false
        })
    },
    updateInfoData() {
      this.submitLoading = true
      this.infoForm
        .put('/user/info/' + this.infoForm.user_id)
        .then(({ data }) => {
          this.dialogInfoFormVisible = false
          this.$notify({
            title: 'Success',
            message: data.meta.message,
            type: 'success',
            duration: 2000
          })
          this.submitLoading = false
        })
        .catch(() => {
          this.submitLoading = false
        })
    },
    handleDelete(row) {
      this.form.fill(row)
      this.$confirm(
        '此操作将永久删除【' + this.form.name + '】角色, 是否继续?',
        '提示',
        {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }
      )
        .then(() => {
          this.listLoading = true
          this.form
            .delete('/role/' + this.form.id)
            .then(({ data }) => {
              this.$notify({
                title: 'Success',
                message: data.meta.message,
                type: 'success',
                duration: 2000
              })
              this.getList()
              this.form.clear()
              this.listLoading = false
            })
            .catch(() => {
              this.listLoading = false
            })
        })
        .catch(() => {
          // this.$message({
          //   type: 'info',
          //   message: '已取消删除'
          // });
        })
    },
    handleAvatarSuccess(res, file) {
      // this.infoForm.avatar = URL.createObjectURL(file.raw)
      this.infoForm.avatar = res.data.url
    },
    beforeAvatarUpload(file) {
      const isJPG = file.type === 'image/jpeg'
      const isLt2M = file.size / 1024 / 1024 < 2

      if (!isJPG) {
        this.$message.error('上传头像图片只能是 JPG 格式!')
      }
      if (!isLt2M) {
        this.$message.error('上传头像图片大小不能超过 2MB!')
      }
      return isJPG && isLt2M
    },
    handleDownload() {
      this.downloadLoading = true
      import('@/vendor/Export2Excel').then(excel => {
        const tHeader = ['ID', '角色', '创建时间', '状态']
        const filterVal = ['id', 'name', 'created_at', 'status']
        this.searchForm
          .get('/role?page=1&psize=1000')
          .then(({ data }) => {
            excel.export_json_to_excel({
              header: tHeader,
              data: this.formatJson(filterVal, data.data),
              filename: 'role-export-data'
            })
            this.downloadLoading = false
          })
          .catch(() => {
            this.downloadLoading = false
          })
      })
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v =>
        filterVal.map(j => {
          if (j === 'timestamp') {
            return parseTime(v[j])
          } else {
            return v[j]
          }
        })
      )
    },
    getSortClass: function(key) {
      const sort = this.listQuery.sort
      return sort === `+${key}`
        ? 'ascending'
        : sort === `-${key}`
          ? 'descending'
          : ''
    }
  }

}
</script>
