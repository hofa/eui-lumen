<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input
        v-model="searchForm.name"
        placeholder="角色"
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
      <el-table-column label="角色" width="150px" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="创建时间" min-width="200px" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.created_at }}</span>
        </template>
      </el-table-column>
      <el-table-column label="IP白名单" width="80px">
        <template slot-scope="{row}">
          <el-tag :type="row.ip_white_enabled | statusFilter">{{ statusOption[row.ip_white_enabled] }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="状态" width="80px">
        <template slot-scope="{row}">
          <el-tag :type="row.status | statusFilter">{{ statusOption[row.status] }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="操作" width="300px" align="center">
        <template slot-scope="{row}">
          <el-button :disabled="btns.edit" type="primary" size="mini" @click="handleUpdate(row)">编辑</el-button>
          <el-button :disabled="btns.delete" size="mini" type="danger" @click="handleDelete(row)">删除</el-button>

          <el-button
            :disabled="btns.permission || row.id==1"
            size="mini"
            type="warning"
            @click="handleMenu(row, $refs)"
          >授权</el-button>
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
          label="角色"
          prop="name"
          :error="form.errors.has('name') ? form.errors.get('name') : ''"
        >
          <el-input v-model="form.name" placeholder="角色" />
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

        <el-form-item
          label="IP白名单"
          prop="ip_white_enabled"
          :error="form.errors.has('ip_white_enabled') ? form.errors.get('ip_white_enabled') : ''"
        >
          <el-select v-model="form.ip_white_enabled" class="filter-item" placeholder="请选择">
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

    <el-dialog title="授权" :visible.sync="dialogFormMenuVisible">
      <el-form
        ref="dataForm"
        label-position="left"
        label-width="70px"
        style="width: 400px; margin-left:50px;"
      >
        <el-tree
          ref="menu"
          :data="menusData"
          show-checkbox
          node-key="id"
          :default-checked-keys="checkedKeys"
          :default-expanded-keys="expandedKeys"
          :props="defaultProps"
        />
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogFormMenuVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="updateMenu()">确定</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Form from '@/utils/form'
import waves from '@/directive/waves' // waves directive
import { parseTime, disable, allow } from '@/utils'
import Pagination from '@/components/Pagination' // secondary package based on el-pagination
// import { mapGetters } from 'vuex'
import { getRefresh } from '@/api/role'
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
        delete: true,
        search: true,
        permission: true,
        export: true
      },
      checkedKeys: [],
      expandedKeys: [],
      menusData: [],
      menuForm: new Form({}),
      roleMenuForm: new Form({
        id: 0,
        ids: []
      }),
      defaultProps: {
        children: 'children',
        label: 'label'
      },
      tableKey: 0,
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
      dialogFormMenuVisible: false,
      dialogStatus: '',
      textMap: {
        update: '编辑',
        create: '新增'
      },
      downloadLoading: false,
      searchForm: new Form({
        name: '',
        status: ''
      }),
      form: new Form({
        id: 0,
        name: '',
        status: 'Normal',
        ip_white_enabled: 'Close'
      }),
      optionForm: new Form({
        ins: 'status'
      }),
      statusOption: [],
      refreshLoading: false
    }
  },
  // computed: {
  //   ...mapGetters(['permission'])
  // },
  mounted() {
    // console.log(this.$store.getters.permission)
    const allowOpen = allow('M:/setting/role')
    // console.log(allowOpen)
    if (allowOpen) {
      this.btns.add = disable('N:Post:/role')
      this.btns.edit = disable('N:Put:/role/{id}')
      this.btns.delete = disable('N:Delete:/role/{id}')
      this.btns.permission = disable('N:Patch:/role/permission/{id}')
      this.btns.search = disable('N:Get:/role')
      this.btns.export = disable('V:/role/export')
      const allowGetOption = allow('N:Get:/option')
      if (!this.btns.permission) {
        this.getMenu()
      }

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
          '/role?page=' +
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
    getMenu() {
      this.listLoading = true
      this.menuForm
        .get('/roleMenu3')
        .then(({ data }) => {
          this.menusData = data.data
          // console.log(this.menusData)
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
      })
    },
    handleCreate() {
      this.form.clear()
      this.dialogStatus = 'create'
      this.dialogFormVisible = true
    },
    createData() {
      this.submitLoading = true
      this.form
        .post('/role')
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
      this.form.clear()
      this.form.fill(row)
      this.dialogStatus = 'update'
      this.dialogFormVisible = true
    },
    handleMenu(row, refs) {
      this.roleMenuForm.id = row.id
      this.dialogFormMenuVisible = true
      this.checkedKeys = row.menu
      this.expandedKeys = row.menu
    },
    updateMenu() {
      this.roleMenuForm.ids = this.$refs.menu.getCheckedKeys()
      this.submitLoading = true
      this.roleMenuForm
        .patch('/role/permission/' + this.roleMenuForm.id)
        .then(({ data }) => {
          this.dialogFormMenuVisible = false
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
    updateData() {
      this.submitLoading = true
      this.form
        .put('/role/' + this.form.id)
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
              this.form.reset()
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
    },
    handleRefresh() {
      this.refreshLoading = true
      getRefresh().then(({ data }) => {
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
    }
  }
}
</script>
