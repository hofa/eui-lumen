<template>
  <div class="app-container">
    <el-tabs v-model="activeName" @tab-click="handleTabClick">
      <el-tab-pane label="文章管理" name="first">
        <div class="filter-container">
          <el-input
            v-model="searchForm.id"
            placeholder="ID"
            style="width: 200px;"
            class="filter-item"
            @keyup.enter.native="handleFilter"
          />
          <el-input
            v-model="searchForm.title"
            placeholder="标题"
            style="width: 200px;"
            class="filter-item"
            @keyup.enter.native="handleFilter"
          />
          <el-input
            v-model="searchForm.username"
            placeholder="用户"
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
          <el-table-column label="标题" width="150px" align="center">
            <template slot-scope="scope">
              <span>{{ scope.row.username }}</span>
            </template>
          </el-table-column>
          <el-table-column label="是否显示" width="150px" align="center">
            <template slot-scope="scope">
              <span>{{ statusOption[scope.row.is_display] }}</span>
            </template>
          </el-table-column>
          <el-table-column label="创建人" width="150px" align="center">
            <template slot-scope="scope">
              <span>{{ scope.row.username }}</span>
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
      </el-tab-pane>
      <el-tab-pane label="新建文章" name="second">
        <el-form
          ref="dataForm"
          label-position="left"
          label-width="70px"
          style="width: 100%; height:100%;"
        >
          <el-form-item
            label="标题"
            prop="title"
            :error="form.errors.has('title') ? form.errors.get('title') : ''"
          >
            <el-input v-model="form.title" placeholder="标题" />
          </el-form-item>

          <el-form-item
            label="内容"
            prop="content"
            :error="form.errors.has('content') ? form.errors.get('content') : ''"
          >
            <mavon-editor style="height: 100%" />
          </el-form-item>

          <el-form-item
            label="Template"
            prop="template"
            :error="form.errors.has('template') ? form.errors.get('template') : ''"
          >
            <el-input v-model="form.template" placeholder="Template" />
          </el-form-item>

          <el-form-item
            label="是否显示"
            prop="is_display"
            :error="form.errors.has('is_display') ? form.errors.get('is_display') : ''"
          >
            <el-select
              v-model="form.is_display"
              placeholder="是否显示"
              clearable
              style="width: 90px"
              class="filter-item"
            >
              <el-option v-for="(item, index) in statusOption" :key="index" :label="item" :value="index" />
            </el-select>
          </el-form-item>

          <el-form-item>
            <el-button @click="dialogFormVisible = false">取消</el-button>
            <el-button
              type="primary"
              :loading="submitLoading"
              @click="dialogStatus==='create'?createData():updateData()"
            >确定</el-button>
          </el-form-item>
        </el-form>

      </el-tab-pane>
    </el-tabs>

  </div>
</template>

<script>
import { mavonEditor } from 'mavon-editor'
import 'mavon-editor/dist/css/index.css'
import Form from '@/utils/form'
import waves from '@/directive/waves' // waves directive
import { disable, allow } from '@/utils'
import Pagination from '@/components/Pagination' // secondary package based on el-pagination
export default {
  name: 'ComplexTable',
  components: { Pagination, mavonEditor },
  directives: { waves },
  filters: {
  },
  data() {
    return {
      activeName: 'first',
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
      dialogStatus: 'create',
      searchForm: new Form({
        title: '',
        id: '',
        username: ''
      }),
      form: new Form({
        id: 0,
        title: '',
        content: '',
        template: '',
        is_display: 'Yes'
      }),
      statusOption: [],
      optionForm: new Form({
        ins: 'status'
      })
    }
  },
  mounted() {
    const allowOpen = allow('M:/contents/article')
    if (allowOpen) {
      this.btns.add = disable('N:Post:/article')
      this.btns.edit = disable('N:Put:/article/{id}')
      this.btns.delete = disable('N:Delete:/article/{id}')
      this.btns.search = disable('N:Get:/article')

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
    handleTabClick() {

    },
    getOptions() {
      this.optionForm.get('/option').then(({ data }) => {
        this.statusOption = data.data.status
      })
    },
    getList() {
      this.listLoading = true
      this.searchForm
        .get(
          '/article?page=' +
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
        .post('/article')
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
        .put('/article/' + this.form.id)
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
            .delete('/article/' + this.form.id)
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
