<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input
        v-model="searchForm.name"
        placeholder="名称"
        style="width: 200px;"
        class="filter-item"
        @keyup.enter.native="handleFilter"
      />
      <el-input
        v-model="searchForm.flag"
        placeholder="标识"
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
      <el-table-column label="标识" width="150px" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.flag }}</span>
        </template>
      </el-table-column>
      <el-table-column label="名称" width="150px" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.name }}</span>
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
          label="名称"
          prop="name"
          :error="form.errors.has('name') ? form.errors.get('name') : ''"
        >
          <el-input v-model="form.name" placeholder="名称" />
        </el-form-item>

        <el-form-item
          label="标识号"
          prop="flag"
          :error="form.errors.has('flag') ? form.errors.get('flag') : ''"
        >
          <el-input v-model="form.flag" placeholder="标识号" />
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
        name: '',
        flag: ''
      }),
      form: new Form({
        id: 0,
        name: '',
        flag: ''
      })
    }
  },
  mounted() {
    const allowOpen = allow('M:/user/channel')
    if (allowOpen) {
      this.btns.add = disable('N:Post:/channel')
      this.btns.edit = disable('N:Put:/channel/{id}')
      this.btns.delete = disable('N:Delete:/channel/{id}')
      this.btns.search = disable('N:Get:/channel')

      if (this.btns.search === false) {
        this.getList()
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
          '/channel?page=' +
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
        .post('/channel')
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
      this.form.reset()
      this.form.fill(row)
      this.dialogStatus = 'update'
      this.dialogFormVisible = true
    },
    updateData() {
      this.submitLoading = true
      this.form
        .put('/channel/' + this.form.id)
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
        '此操作将永久删除【' + this.form.name + '】渠道, 是否继续?',
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
            .delete('/channel/' + this.form.id)
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
