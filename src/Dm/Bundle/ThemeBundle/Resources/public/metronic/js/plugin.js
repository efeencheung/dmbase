$(document).ready(function(){
    /* 删除标准提示 */
    $('.delete-link').confirmation({
        title: '您确定删除吗？',
        btnOkLabel: '删除',
        btnOkIcon: '',
        btnCancelLabel: '取消',
        btnCancelIcon: '',
        onConfirm: function(event) {
            var did = this.did;

            $('#delete_form_hidden_'+did).submit();
        }
    });
});
