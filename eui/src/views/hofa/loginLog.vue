<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input
        v-model="searchForm.username"
        placeholder="用户名"
        style="width: 120px;"
        class="filter-item"
        @keyup.enter.native="handleFilter"
      />
      <el-input
        v-model="searchForm.ip"
        placeholder="IP"
        style="width: 120px;"
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
        v-model="searchForm.type"
        placeholder="类型"
        clearable
        style="width: 90px"
        class="filter-item"
      >
        <el-option v-for="(item, index) in typeOption" :key="index" :label="item" :value="index" />
      </el-select>
      <el-date-picker
        v-model="searchForm.created_at"
        type="daterange"
        align="right"
        unlink-panels
        range-separator="-"
        start-placeholder="登录开始日期"
        end-placeholder="登录结束日期"
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
        v-waves
        :disabled="btns.unlock"
        class="filter-item"
        type="primary"
        icon="el-icon-download"
        @click="handleUnlock"
      >解封</el-button>
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
      <el-table-column label="用户名" width="150px" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.username }}</span>
        </template>
      </el-table-column>
      <el-table-column label="IP" width="150px" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.ip }}</span>
        </template>
      </el-table-column>
      <el-table-column prop="created_at" sortable="custom" label="登录时间" min-width="150px" align="center" :class-name="getSortClass('created_at')">
        <template slot-scope="scope">
          <span>{{ scope.row.created_at }}</span>
        </template>
      </el-table-column>
      <el-table-column label="状态" width="120px">
        <template slot-scope="{row}">
          <el-tag :type="row.status | statusFilter">{{ statusOption[row.status] }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="类型" width="150px">
        <template slot-scope="{row}">
          <el-tag :type="row.type | typeFilter">{{ typeOption[row.type] }}</el-tag>
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

    <el-dialog title="解封" :visible.sync="dialogFormVisible">
      <el-form
        ref="dataForm"
        label-position="left"
        label-width="70px"
        style="width: 400px; margin-left:50px;"
      >
        <el-form-item
          label="解封"
          prop="value"
          :error="form.errors.has('value') ? form.errors.get('value') : ''"
        >
          <el-input v-model="form.value" placeholder="请填入账号名或者IP" />
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogFormVisible = false">取消</el-button>
        <el-button
          type="primary"
          :loading="submitLoading"
          @click="createData()"
        >确定</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Form from '@/utils/form'
import waves from '@/directive/waves' // waves directive
import { parseTime, disable, allow } from '@/utils'
import Pagination from '@/components/Pagination' // secondary package based on el-pagination
export default {
  name: 'ComplexTable',
  components: { Pagination },
  directives: { waves },
  filters: {
    statusFilter(status) {
      const statusMap = {
        Succ: 'success'
      }
      return statusMap[status] || 'danger'
    },
    typeFilter(type) {
      const typeMap = {
        Normal: 'success'
      }
      return typeMap[type] || 'danger'
    }
  },
  data() {
    return {
      btns: {
        search: true,
        export: true,
        unlock: true
      },
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
      listQuery: {
        page: 1,
        limit: 20,
        sort: '*id'
      },
      downloadLoading: false,
      submitLoading: false,
      searchForm: new Form({
        username: '',
        status: '',
        ip: '',
        type: '',
        created_at: ''
      }),
      form: new Form({
        value: ''
      }),
      optionForm: new Form({
        ins: 'sf,loginLogType'
      }),
      statusOption: [],
      typeOption: [],
      dialogFormVisible: false
    }
  },

  mounted() {
    const allowOpen = allow('M:/log/login')
    if (allowOpen) {
      this.btns.search = disable('N:Get:/loginLog')
      this.btns.export = disable('V:/loginLog/export')
      this.btns.unlock = disable('N:Post:/loginLog/unlock')
      const allowGetOption = allow('N:Get:/option')

      if (this.btns.search === false) {
        this.getList()
      }

      if (allowGetOption) {
        this.getOptions()
      }
    } else {
      this.$router.push('/401')
    }
  },

  methods: {
    getList() {
      this.listLoading = true
      this.searchForm
        .get(
          '/loginLog?page=' +
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
      } else if (prop === 'created_at') {
        this.sortByCreatedAt(order)
      }
    },
    sortByCreatedAt(order) {
      if (order === 'ascending') {
        this.listQuery.sort = '*created_at'
      } else {
        this.listQuery.sort = '-created_at'
      }
      this.handleFilter()
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
        this.statusOption = data.data.sf
        this.typeOption = data.data.loginLogType
      })
    },
    handleUnlock() {
      this.dialogFormVisible = true
    },
    createData() {
      this.submitLoading = true
      this.form.post('/loginLog/unlock').then(({ data }) => {
        this.$notify({
          title: 'Success',
          message: data.meta.message,
          type: 'success',
          duration: 2000
        })
        this.submitLoading = false
        this.dialogFormVisible = false
      }).catch(() => {
        this.submitLoading = false
      })
    },
    handleDownload() {
      this.downloadLoading = true
      import('@/vendor/Export2Excel').then(excel => {
        const tHeader = ['ID', '用户名', 'ip', '登录时间', '状态', '类型']
        const filterVal = ['id', 'username', 'ip', 'created_at', 'status', 'type']
        this.searchForm
          .get('/loginLog?page=1&psize=1000')
          .then(({ data }) => {
            excel.export_json_to_excel({
              header: tHeader,
              data: this.formatJson(filterVal, data.data),
              filename: 'login-log-export-data'
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
