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
      <el-input
        v-model="searchForm.recipient"
        placeholder="收件人"
        style="width: 200px;"
        class="filter-item"
        @keyup.enter.native="handleFilter"
      />
      <el-input
        v-model="searchForm.mobile"
        placeholder="收件号码"
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
      <el-table-column label="账号" min-width="100px" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.username }}</span>
        </template>
      </el-table-column>
      <el-table-column label="收件人" width="100px" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.recipient }}</span>
        </template>
      </el-table-column>
      <el-table-column label="收件号码" width="150px" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.mobile }}</span>
        </template>
      </el-table-column>
      <el-table-column label="详细地址" width="350px" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.province }} {{ scope.row.city }} {{ scope.row.area }} {{ scope.row.street }} {{ scope.row.address }}</span>
        </template>
      </el-table-column>
      <el-table-column label="创建时间" min-width="150px" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.created_at }}</span>
        </template>
      </el-table-column>
      <el-table-column label="操作" width="150px" align="center">
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
        >
          <el-input v-model="form.username" placeholder="账号" />
        </el-form-item>

        <el-form-item
          label="省份"
          prop="province"
          :error="form.errors.has('province') ? form.errors.get('province') : ''"
        >
          <el-select
            v-model="form.province"
            filterable
            placeholder="省份"
            clearable
            style="width: 220px"
            class="filter-item"
            @change="handleProvince"
          >
            <el-option v-for="(item, index) in provinceOption" :key="index" :label="item" :value="item" />
          </el-select>
        </el-form-item>
        <el-form-item
          label="市"
          prop="city"
          :error="form.errors.has('city') ? form.errors.get('city') : ''"
        >
          <el-select
            v-model="form.city"
            filterable
            placeholder="市"
            clearable
            style="width: 220px"
            class="filter-item"
            @change="handleCity"
          >
            <el-option v-for="(item, index) in cityOption" :key="index" :label="item" :value="item" />
          </el-select>
        </el-form-item>
        <el-form-item
          label="地区"
          prop="area"
          :error="form.errors.has('area') ? form.errors.get('area') : ''"
        >
          <el-select
            v-model="form.area"
            filterable
            placeholder="地区"
            clearable
            style="width: 220px"
            class="filter-item"
            @change="handleArea"
          >
            <el-option v-for="(item, index) in areaOption" :key="index" :label="item" :value="item" />
          </el-select>
        </el-form-item>
        <el-form-item
          label="街道"
          prop="street"
          :error="form.errors.has('street') ? form.errors.get('street') : ''"
        >
          <el-select
            v-model="form.street"
            filterable
            placeholder="街道"
            clearable
            style="width: 220px"
            class="filter-item"
          >
            <el-option v-for="(item, index) in streetOption" :key="index" :label="item" :value="item" />
          </el-select>
        </el-form-item>

        <el-form-item
          label="详细地址"
          prop="address"
          :error="form.errors.has('address') ? form.errors.get('address') : ''"
        >
          <el-input v-model="form.address" placeholder="详细地址" />
        </el-form-item>
        <el-form-item
          label="邮编"
          prop="zip_code"
          :error="form.errors.has('zip_code') ? form.errors.get('zip_code') : ''"
        >
          <el-input v-model="form.zip_code" placeholder="邮编" />
        </el-form-item>
        <el-form-item
          label="手机号码"
          prop="mobile"
          :error="form.errors.has('mobile') ? form.errors.get('mobile') : ''"
        >
          <el-input v-model="form.mobile" placeholder="手机号码" />
        </el-form-item>
        <el-form-item
          label="收货人"
          prop="recipient"
          :error="form.errors.has('recipient') ? form.errors.get('recipient') : ''"
        >
          <el-input v-model="form.recipient" placeholder="收货人" />
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
var pcas = require('./pcas.json')
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
        recipient: '',
        mobile: ''
      }),
      form: new Form({
        id: 0,
        province: '',
        city: '',
        area: '',
        street: '',
        address: '',
        mobile: '',
        is_default: 'Yes',
        recipient: '',
        zip_code: '',
        username: ''
      }),
      provinceOption: [],
      cityOption: [],
      areaOption: [],
      streetOption: [],
      ynOption: [],
      optionForm: new Form({
        ins: 'yn'
      })
    }
  },
  mounted() {
    for (var i in pcas) {
      this.provinceOption.push(i)
    }
    const allowOpen = allow('M:/user/address')
    if (allowOpen) {
      this.btns.add = disable('N:Post:/userAddress')
      this.btns.edit = disable('N:Put:/userAddress/{id}')
      this.btns.delete = disable('N:Delete:/userAddress/{id}')
      this.btns.search = disable('N:Get:/userAddress')

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
    handleProvince() {
      let j = 0
      this.cityOption = []
      for (var i in pcas[this.form.province]) {
        j++
        if (j === 1 && this.dialogStatus === 'create') {
          this.form.city = i
        }
        this.cityOption.push(i)
      }
      this.handleCity()
      this.handleArea()
    },
    handleCity() {
      let j = 0
      this.areaOption = []
      for (var i in pcas[this.form.province][this.form.city]) {
        j++
        if (j === 1 && this.dialogStatus === 'create') {
          this.form.area = i
        }
        this.areaOption.push(i)
      }
      this.handleArea()
    },
    handleArea() {
      // let j = 0
      this.streatOption = []
      if (this.dialogStatus === 'create') {
        this.form.street = ''
      }
      for (var i in pcas[this.form.province][this.form.city][this.form.area]) {
        // j++
        // if (j === 1) {
        //   this.form.street = pcas[this.form.province][this.form.city][this.form.area][i]
        // }
        this.streetOption.push(pcas[this.form.province][this.form.city][this.form.area][i])
      }
    },
    getList() {
      this.listLoading = true
      this.searchForm
        .get(
          '/userAddress?page=' +
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
        .post('/userAddress')
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
      this.handleProvince()
      this.handleCity()
      this.handleArea()
      this.dialogStatus = 'update'
      this.dialogFormVisible = true
    },
    updateData() {
      this.submitLoading = true
      this.form
        .put('/userAddress/' + this.form.id)
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
        '此操作将永久删除【' + this.form.name + '】层级, 是否继续?',
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
            .delete('/userAddress/' + this.form.id)
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
