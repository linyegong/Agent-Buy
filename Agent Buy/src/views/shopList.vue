<template>
  <div class="shop-list">
    <h2>店铺列表</h2>
    <el-row>
      <el-col :span="4">
        <el-button type="primary">批量操作</el-button>
        <el-button type="primary">应用</el-button>
      </el-col>
      <el-col :span="20" style="text-align: right;">
        <span>1项</span>
      </el-col>
    </el-row>

    <el-table :data="tableData" style="width: 100%">
      <el-table-column prop="image" label="图像" width="180">
        <template slot-scope="scope">
          <img src="https://placehold.co/60x60" alt="店铺图像" />
        </template>
      </el-table-column>
      <el-table-column prop="name" label="名称" width="180"></el-table-column>
      <el-table-column prop="description" label="描述"></el-table-column>
      <el-table-column prop="status" label="状态"></el-table-column>
      <el-table-column prop="sort" label="排序"></el-table-column>
    </el-table>

    <el-dialog title="编辑店铺信息" :visible.sync="dialogVisible" width="30%">
      <el-form :model="form">
        <el-form-item label="名称">
          <el-input v-model="form.name"></el-input>
        </el-form-item>
        <el-form-item label="描述">
          <el-input v-model="form.description"></el-input>
        </el-form-item>
        <el-form-item label="状态">
          <el-checkbox v-model="form.status"></el-checkbox>
        </el-form-item>
        <el-form-item label="图片">
          <el-upload action="https://jsonplaceholder.typicode.com/posts/" list-type="picture-card" :on-preview="handlePictureCardPreview" :on-remove="handleRemove">
            <i class="el-icon-plus"></i>
          </el-upload>
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="saveForm">保存</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
export default {
  data() {
    return {
      tableData: [
      ],
      dialogVisible: false,
      form: {
        name: '',
        description: '',
        status: false,
        image: ''
      }
    };
  },
  methods: {
    saveForm() {
      // 处理保存逻辑
      this.dialogVisible = false;
    },
    handleRemove(file, fileList) {
      console.log(file, fileList);
    },
    handlePictureCardPreview(file) {
      this.dialogImageUrl = file.url;
      this.dialogImageVisible = true;
    }
  }
};
</script>

<style scoped>
.shop-list {
  padding: 20px;
}
.el-table {
  margin-top: 20px;
}
</style>