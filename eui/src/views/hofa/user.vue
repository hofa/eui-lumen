<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input
        v-model="searchForm.username"
        placeholder="账号"
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
      <el-table-column
        label="ID"
        prop="id"
        sortable="custom"
        align="center"
        width="80"
        :class-name="getSortClass('id')"
      >
        <template slot-scope="scope">
          <span>{{ scope.row.id }}</span>
        </template>
      </el-table-column>
      <el-table-column label="账号" width="150px" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.username }}</span>
        </template>
      </el-table-column>
      <el-table-column label="创建时间" min-width="200px" align="center">
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
  </div>
</template>

<style>
.el-scrollbar__wrap {
  overflow-x: hidden;
}
</style>

<script>
import Form from '@/utils/form'
import waves from '@/directive/waves' // waves directive
import { parseTime, getObjectKeys, allow, disable } from '@/utils'
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
      btns: {
        add: true,
        edit: true,
        search: true,
        export: true
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
        sort: '*id'
      },
      dialogFormVisible: false,
      dialogStatus: '',
      textMap: {
        update: '编辑',
        create: '新增'
      },
      downloadLoading: false,
      searchForm: new Form({
        username: '',
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
      optionForm: new Form({
        ins: 'status,role'
      }),
      statusOption: [],
      roleOption: []
    }
  },
  // computed: {
  //   ...mapGetters(['permission'])
  // },
  mounted() {
    const allowOpen = allow('M:/user/user')
    if (allowOpen) {
      this.btns.add = disable('N:Post:/user')
      this.btns.edit = disable('N:Put:/user/{id}')
      this.btns.search = disable('N:Get:/user')
      this.btns.export = disable('V:/user/export')
      const allowGetOption = allow('N:Get:/option')
      if (!this.btns.search) {
        this.getList()
      }

      if (allowGetOption) {
        this.getOptions()
      }
    } else {
      // console.log('allowOpen', allowOpen)
      this.$router.push('/401')
    }
  },
  methods: {
    handleCheckAllChange(val) {
      this.form.role = val ? getObjectKeys(this.roleOption) : []
      // console.log(this.form.role)
      this.isIndeterminate = false
    },
    handleCheckedRoleChange(value) {
      // console.log(value)
      // let checkedCount = Object.keys(value).length
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
    },
    sortByID(order) {
      if (order === 'ascending') {
        this.listQuery.sort = '*id'
      } else {
        this.listQuery.sort = '-id'
      }
      this.handleFilter()
    },
    getOptions() {
      this.optionForm.get('/option').then(({ data }) => {
        this.statusOption = data.data.status
        this.roleOption = data.data.role
      })
    },
    handleCreate() {
      this.form.clear()
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
          this.form.clear()
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
    updateData() {
      // this.listLoading = false
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
          this.form.clear()
          // this.listLoading = false
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
