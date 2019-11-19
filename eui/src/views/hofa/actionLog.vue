<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input
        v-model="searchForm.action_username"
        placeholder="操作账号"
        style="width: 100px;"
        class="filter-item"
        @keyup.enter.native="handleFilter"
      />
      <el-input
        v-model="searchForm.username"
        placeholder="被操作账号"
        style="width: 100px;"
        class="filter-item"
        @keyup.enter.native="handleFilter"
      />
      <el-input
        v-model="searchForm.ip"
        placeholder="IP"
        style="width: 100px;"
        class="filter-item"
        @keyup.enter.native="handleFilter"
      />
      <el-select
        v-model="searchForm.module_id"
        placeholder="节点类型"
        clearable
        filterable
        style="width: 220px"
        class="filter-item"
      >
        <el-option v-for="(item, index) in actionTypeOption" :key="index" :label="item" :value="index" />
      </el-select>
      <el-date-picker
        v-model="searchForm.created_at"
        type="daterange"
        align="right"
        unlink-panels
        range-separator="-"
        start-placeholder="操作开始日期"
        end-placeholder="操作结束日期"
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
      <el-table-column type="expand">
        <template slot-scope="props">
          <el-form label-position="left" inline class="demo-table-expand">
            <el-form-item label="JSON数据">
              <span>{{ props.row.diff }}</span>
            </el-form-item>
          </el-form>
        </template>
      </el-table-column>
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
      <el-table-column label="操作账号" width="120px" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.action_username }}</span>
        </template>
      </el-table-column>
      <el-table-column label="被操作账号" width="120px" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.username }}</span>
        </template>
      </el-table-column>
      <el-table-column label="IP" width="100px" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.ip }}</span>
        </template>
      </el-table-column>
      <el-table-column prop="created_at" sortable="custom" label="操作时间" min-width="150px" align="center" :class-name="getSortClass('created_at')">
        <template slot-scope="scope">
          <span>{{ scope.row.created_at }}</span>
        </template>
      </el-table-column>
      <el-table-column label="操作节点" width="200px">
        <template slot-scope="{row}">
          {{ actionTypeOption[row.module_id] }}
        </template>
      </el-table-column>
      <el-table-column label="备注" width="120px">
        <template slot-scope="scope">
          <span>{{ scope.row.mark }}</span>
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

  },
  data() {
    return {
      btns: {
        search: true,
        export: true
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
        sort: '-id'
      },
      downloadLoading: false,
      searchForm: new Form({
        action_username: '',
        username: '',
        ip: '',
        module_id: '',
        created_at: ''
      }),
      optionForm: new Form({
        ins: 'actionType'
      }),
      actionTypeOption: []
    }
  },

  mounted() {
    const allowOpen = allow('M:/log/action')
    if (allowOpen) {
      this.btns.search = disable('N:Get:/actionLog')
      this.btns.export = disable('V:/actionLog/export')
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
          '/actionLog?page=' +
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
        this.actionTypeOption = data.data.actionType
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
