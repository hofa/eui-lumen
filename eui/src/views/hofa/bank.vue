<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input
        v-model="searchForm.username"
        placeholder="用户"
        style="width: 200px;"
        class="filter-item"
        @keyup.enter.native="handleFilter"
      />
      <el-input
        v-model="searchForm.bank_card"
        placeholder="卡号"
        style="width: 200px;"
        class="filter-item"
        @keyup.enter.native="handleFilter"
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
        :disabled="btns.add"
        class="filter-item"
        style="margin-left: 10px;"
        type="primary"
        icon="el-icon-edit"
        @click="handleCreate"
      >新增</el-button>
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
      <el-table-column label="用户" width="150px" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.username }}</span>
        </template>
      </el-table-column>
      <el-table-column label="银行" width="150px" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.bank_name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="支行" width="150px" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.bank_branch }}</span>
        </template>
      </el-table-column>
      <el-table-column label="卡号" width="150px" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.bank_card }}</span>
        </template>
      </el-table-column>
      <el-table-column label="收款人" width="150px" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.real_name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="是否默认" width="150px" align="center">
        <template slot-scope="scope">
          <span>{{ ynOption[scope.row.is_default] }}</span>
        </template>
      </el-table-column>
      <el-table-column label="创建时间" min-width="200px" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.created_at }}</span>
        </template>
      </el-table-column>
      <el-table-column label="操作" width="300px" align="center">
        <template slot-scope="{row}">
          <el-button :disabled="btns.edit" type="primary" size="mini" @click="handleUpdate(row)">编辑</el-button>
          <el-button :disabled="btns.delete" size="mini" type="danger" @click="handleDelete(row)">删除</el-button>
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
          :disabled="dialogStatus==='create'?false:true"
        >
          <el-input v-model="form.username" placeholder="账号" />
        </el-form-item>

        <el-form-item
          label="银行名称"
          prop="bank_name"
          :error="form.errors.has('bank_name') ? form.errors.get('bank_name') : ''"
        >
          <el-input v-model="form.bank_name" placeholder="银行名称" />
        </el-form-item>

        <el-form-item
          label="支行名称"
          prop="bank_branch"
          :error="form.errors.has('bank_branch') ? form.errors.get('bank_branch') : ''"
        >
          <el-input v-model="form.bank_branch" placeholder="支行名称" />
        </el-form-item>

        <el-form-item
          label="银行卡号"
          prop="bank_card"
          :error="form.errors.has('bank_card') ? form.errors.get('bank_card') : ''"
        >
          <el-input v-model="form.bank_card" placeholder="银行卡号" />
        </el-form-item>

        <el-form-item
          label="收款人"
          prop="real_name"
          :error="form.errors.has('real_name') ? form.errors.get('real_name') : ''"
        >
          <el-input v-model="form.real_name" placeholder="收款人" />
        </el-form-item>

        <el-form-item
          label="是否默认"
          prop="is_default"
          :error="form.errors.has('is_default') ? form.errors.get('is_default') : ''"
        >
          <el-select
            v-model="form.is_default"
            placeholder="是否默认"
            clearable
            style="width: 90px"
            class="filter-item"
          >
            <el-option v-for="(item, index) in ynOption" :key="index" :label="item" :value="index" />
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

<script>
import Form from '@/utils/form'
import waves from '@/directive/waves' // waves directive
import { disable, allow } from '@/utils'
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
        edit: true,
        delete: true,
        search: true
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
      dialogStatus: '',
      textMap: {
        update: '编辑',
        create: '新增'
      },
      searchForm: new Form({
        username: '',
        bank_card: ''
      }),
      form: new Form({
        id: 0,
        username: '',
        bank_name: '',
        bank_branch: '',
        bank_card: '',
        real_name: '',
        is_default: 'Yes'
      }),
      ynOption: [],
      optionForm: new Form({
        ins: 'yn'
      })
    }
  },
  mounted() {
    const allowOpen = allow('M:/user/bank')
    if (allowOpen) {
      this.btns.add = disable('N:Post:/userBank')
      this.btns.edit = disable('N:Put:/userBank/{id}')
      this.btns.delete = disable('N:Delete:/userBank/{id}')
      this.btns.search = disable('N:Get:/userBank')

      if (this.btns.search === false) {
        this.getList()
      }
      const allowGetOption = allow('N:Get:/option')

      if (allowGetOption) {
        this.getOptions()
      }
    } else {
      this.$router.push('/401')
    }
  },

  methods: {
    getOptions() {
      this.optionForm.get('/option').then(({ data }) => {
        this.ynOption = data.data.yn
      })
    },
    getList() {
      this.listLoading = true
      this.searchForm
        .get(
          '/userBank?page=' +
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
    handleCreate() {
      this.form.reset()
      this.dialogStatus = 'create'
      this.dialogFormVisible = true
    },
    createData() {
      this.submitLoading = true
      this.form
        .post('/userBank')
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
    updateData() {
      this.submitLoading = true
      this.form
        .put('/userBank/' + this.form.id)
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
        '此操作将永久删除【' + this.form.bank_card + '】银行卡, 是否继续?',
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
            .delete('/userBank/' + this.form.id)
            .then(({ data }) => {
              this.$notify({
                title: 'Success',
                message: data.meta.message,
                type: 'success',
                duration: 2000
              })
              this.getList()
              this.listLoading = false
            })
            .catch(() => {
              this.listLoading = false
            })
        })
        .catch(() => {
          this.listLoading = false
        })
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
