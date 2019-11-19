<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input
        v-model="searchForm.ip"
        placeholder="IP"
        style="width: 100px;"
        class="filter-item"
        @keyup.enter.native="handleFilter"
      />
      <el-select
        v-model="searchForm.type"
        placeholder="类型"
        clearable
        filterable
        style="width: 220px"
        class="filter-item"
      >
        <el-option v-for="(item, index) in typeOption" :key="index" :label="item" :value="index" />
      </el-select>
      <el-button
        v-waves
        :disabled="btns.search"
        class="filter-item"
        type="primary"
        icon="el-icon-search"
        @click="handleFilter"
      >搜索</el-button>
      <el-button
        :disabled="btns.add"
        class="filter-item"
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
      <el-button
        v-waves
        :loading="refreshLoading"
        class="filter-item"
        type="primary"
        icon="el-icon-refresh"
        @click="handleRefresh"
      >刷新角色缓存</el-button>
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
      <el-table-column label="角色" width="120px" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.action_username }}</span>
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
      <el-table-column label="类型" width="200px">
        <template slot-scope="{row}">
          {{ typeOption[row.type] }}
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

    <el-dialog title="IP名单增加" :visible.sync="dialogFormVisible">
      <el-form
        ref="dataForm"
        label-position="left"
        label-width="70px"
        style="width: 400px; margin-left:50px;"
      >
        <el-form-item
          label="IP"
          prop="ips"
          :error="form.errors.has('ips') ? form.errors.get('ips') : ''"
        >
          <el-input v-model="form.ips" type="textarea" placeholder="IP(一行一个)" />
        </el-form-item>
        <el-form-item
          label="应用角色"
          prop="role_id"
          :error="form.errors.has('role_id') ? form.errors.get('role_id') : ''"
        >
          <el-select v-model="form.role_id" class="filter-item" placeholder="请选择">
            <el-option
              v-for="(item, index) in roleOption"
              :key="index"
              :label="item"
              :value="index"
            />
          </el-select>
        </el-form-item>
        <el-form-item
          label="类型"
          prop="type"
          :error="form.errors.has('type') ? form.errors.get('type') : ''"
        >
          <el-select v-model="form.type" class="filter-item" placeholder="请选择">
            <el-option
              v-for="(item, index) in typeOption"
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
          @click="createData()"
        >确定</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { getIPBlackWhiteListRefresh } from '@/api/role'
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
        add: true,
        delete: true,
        search: true,
        export: true,
        refresh: true
      },
      tableKey: 0,
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
      form: new Form({
        id: 0,
        role_id: 0,
        type: 'White',
        ips: ''
      }),
      optionForm: new Form({
        ins: 'IPBlackWhiteListType,roleByIP'
      }),
      typeOption: [],
      roleOption: [],
      dialogFormVisible: false,
      refreshLoading: false,
      submitLoading: false
    }
  },

  mounted() {
    const allowOpen = allow('M:/log/IPBlackWhiteList')
    if (allowOpen) {
      this.btns.add = disable('N:Get:/IPBlackWhiteList')
      this.btns.del = disable('N:Delete:/IPBlackWhiteList/{id}')
      this.btns.search = disable('N:Get:/IPBlackWhiteList')
      this.btns.export = disable('V:/IPBlackWhiteList/export')
      this.btns.refresh = disable('N:Post:/IPBlackWhiteList/refresh')
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
    handleRefresh() {
      this.refreshLoading = true
      getIPBlackWhiteListRefresh().then(({ data }) => {
        this.$notify({
          title: 'Success',
          message: data.meta.message,
          type: 'success',
          duration: 2000
        })
        this.refreshLoading = false
      }).catch(() => {
        this.refreshLoading = false
      })
    },
    handleCreate() {
      this.form.reset()
      this.dialogFormVisible = true
    },
    createData() {
      this.submitLoading = true
      this.form
        .post('/IPBlackWhiteList')
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
    getList() {
      this.listLoading = true
      this.searchForm
        .get(
          '/IPBlackWhiteList?page=' +
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
        this.typeOption = data.data.IPBlackWhiteListType
        this.roleOption = data.data.roleByIP
      })
    },
    handleDownload() {
      this.downloadLoading = true
      import('@/vendor/Export2Excel').then(excel => {
        const tHeader = ['ID', 'IP', '创建时间', '角色', '类型']
        const filterVal = ['id', 'ip', 'created_at', 'role_id', 'type']
        this.searchForm
          .get('/IPBlackWhiteList?page=1&psize=1000')
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
