<template>
  <div class="app-container">
    <el-row :gutter="20">
      <el-col :span="18">
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
              <el-table-column label="标题" width="250px" align="center">
                <template slot-scope="scope">
                  <span>{{ scope.row.title }}</span>
                </template>
              </el-table-column>
              <el-table-column label="是否显示" width="150px" align="center">
                <template slot-scope="scope">
                  <span>{{ statusOption[scope.row.status] }}</span>
                </template>
              </el-table-column>
              <el-table-column label="创建人" width="150px" align="center">
                <template slot-scope="scope">
                  <span>{{ scope.row.username }}</span>
                </template>
              </el-table-column>
              <el-table-column label="创建时间" min-width="120px" align="center">
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
          <el-tab-pane :label="dialogStatus==='create' ? '新建文章' : '修改文章'" name="second">
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
                <mavon-editor ref="md" v-model="form.content" style="height: 100%" @imgAdd="$imgAdd" @imgDel="$imgDel" />
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
                prop="status"
                :error="form.errors.has('status') ? form.errors.get('status') : ''"
              >
                <el-select
                  v-model="form.status"
                  placeholder="是否显示"
                  clearable
                  style="width: 90px"
                  class="filter-item"
                >
                  <el-option v-for="(item, index) in statusOption" :key="index" :label="item" :value="index" />
                </el-select>
              </el-form-item>

              <el-form-item>
                <el-button v-show="dialogStatus == 'update'" @click="dialogStatus = 'create'">取消</el-button>
                <el-button
                  type="primary"
                  :loading="submitLoading"
                  @click="dialogStatus==='create'?createData():updateData()"
                >确定</el-button>
              </el-form-item>
            </el-form>

          </el-tab-pane>
        </el-tabs>
      </el-col>

      <el-col :span="6">
        <el-input
          v-model="filterText"
          placeholder="输入关键字进行过滤"
        />
        <el-tree
          ref="menu"
          :data="menusData"
          show-checkbox
          node-key="id"
          default-expand-all
          :expand-on-click-node="false"
          :filter-node-method="filterNode"
          @check-change="handleNavCheckChange"
        >
          <span slot-scope="{ node }" class="custom-tree-node">
            <span>
              {{ node.label }}
            </span>
          </span>
        </el-tree>
      </el-col>
    </el-row>
  </div>
</template>

<script>
import { upload, removeUpload } from '@/api/upload'
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
        sort: '-id'
      },
      dialogStatus: 'create',
      searchForm: new Form({
        title: '',
        id: '',
        username: '',
        nav_ids: []
      }),
      form: new Form({
        id: 0,
        title: '',
        content: '',
        template: '',
        status: 'Normal',
        nav_ids: []
      }),
      statusOption: [],
      optionForm: new Form({
        ins: 'status'
      }),
      menusData: [],
      filterText: '',
      checkedKeys: []
    }
  },
  watch: {
    filterText(val) {
      this.$refs.menu.filter(val)
    }
  },
  mounted() {
    const allowOpen = allow('M:/contents/article')
    if (allowOpen) {
      this.btns.add = disable('N:Post:/article')
      this.btns.edit = disable('N:Put:/article/{id}')
      this.btns.delete = disable('N:Delete:/article/{id}')
      this.btns.search = disable('N:Get:/article')
      this.btns.nav = disable('N:Get:/article/nav')

      if (this.btns.search === false) {
        this.getList()
      }
      const allowGetOption = allow('N:Get:/option')

      if (allowGetOption) {
        this.getOptions()
      }

      if (this.btns.nav === false) {
        this.getNav()
      }
    } else {
      this.$router.push('/401')
    }
  },
  methods: {
    handleNavCheckChange(data, checked, indeterminate) {
      if (indeterminate !== false) {
        return
      }
      this.form.nav_ids = this.$refs.menu.getCheckedKeys()
      this.searchForm.nav_ids = this.$refs.menu.getCheckedKeys()
      if (this.activeName === 'first') {
        this.getList()
      }
    },
    getNav() {
      this.searchForm
        .get('/article/nav')
        .then(({ data }) => {
          this.menusData = data.data
        })
        .catch(() => {
        })
    },
    filterNode(value, data) {
      if (!value) return true
      return data.label.indexOf(value) !== -1
    },
    $imgDel($file) {
      removeUpload({ 'url': $file }).then(({ data }) => {
      }).catch(() => {

      })
    },
    // 绑定@imgAdd event
    $imgAdd(pos, $file) {
      // 第一步.将图片上传到服务器.
      var formdata = new FormData()
      formdata.append('file', $file)
      upload(formdata).then(({ data }) => {
        // 第二步.将返回的url替换到文本原位置![...](0) -> ![...](url)
        /**
         * $vm 指为mavonEditor实例，可以通过如下两种方式获取
         * 1. 通过引入对象获取: `import {mavonEditor} from ...` 等方式引入后，`$vm`为`mavonEditor`
         * 2. 通过$refs获取: html声明ref : `<mavon-editor ref=md ></mavon-editor>，`$vm`为 `this.$refs.md`
         */
        // console.log(data.data.url)
        this.$refs.md.$img2Url(pos, data.data.url)
      }).catch(() => {

      })
    },
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
      this.form.reset()
      this.form.fill(row)
      // this.checkedKeys = row.nav_ids
      this.$refs.menu.setCheckedKeys([8])
      this.dialogStatus = 'update'
      this.activeName = 'second'
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
