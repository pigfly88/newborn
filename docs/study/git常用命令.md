## git使用指南 ##
1. 打开git命令行操作窗口，利用cd命令切换到对应的文件夹（代码根目录）
	>`cd E:/study/h5`

2. 从git仓库**拉取代码**到本地电脑
	>`git clone https://github.com/justlikeheaven/newborn.git`
 
3. **更新代码**
	>`git pull` 

4. **提交代码**
    >`git add . 或者 git add -A(删除也更新上去)`  
	>`git commit -m "这里添加注释"`  
    >`git push origin master`
	
5. **引用子仓库**
例如，在项目Game中有一个子目录AI。Game和AI分别是一个独立的git项目，可以分开维护。为了避免直接复制粘贴代码，我们希望Game中的AI子目录与AI的git项目关联，有3层意思：

AI子目录使用AI的git项目来填充，内容保持一致。
当AI的git项目代码有更新，可以拉取更新到Game项目的AI子目录来。
反过来，当Game项目的AI子目录有变更，还可以推送这些变更到AI的git项目。
用git subtree可以轻松满足上面的需求。

git subtree用法

针对第一段的3条需求，我分别说明具体的命令。

1. 第一次添加子目录，建立与git项目的关联
建立关联总共有2条命令。

语法：git remote add -f <子仓库名> <子仓库地址>

解释：其中-f意思是在添加远程仓库之后，立即执行fetch。

语法：git subtree add --prefix=<子目录名> <子仓库名> <分支> --squash

解释：–squash意思是把subtree的改动合并成一次commit，这样就不用拉取子项目完整的历史记录。–prefix之后的=等号也可以用空格。

示例

$git remote add -f ai https://github.com/aoxu/ai.git  
$git subtree add --prefix=ai ai master --squash
2. 从远程仓库更新子目录
更新子目录有2条命令。

语法：git fetch <远程仓库名> <分支>

语法：git subtree pull --prefix=<子目录名> <远程分支> <分支> --squash

示例

$git fetch ai master  
$git subtree pull --prefix=ai ai --squash
3. 从子目录push到远程仓库（确认你有写权限）
推送子目录的变更有1条命令。

语法：git subtree push --prefix=<子目录名> <远程分支名> 分支

示例

$git subtree push --prefix=ai ai master

======================================================
git 忽略已经提交的文件
git update-index --assume-unchanged <files>

======================================================
ssh方式
1 如果没有安装ssh，那么使用下面的指令

sudo apt-get install ssh

2 检查SSH公钥

cd ~/.ssh
看看存不存在.ssh，如果存在的话，掠过下一步；不存在的请看下一步

3 生成SSH公钥
$ ssh-keygen -t rsa -C "your_email@youremail.com" 
# Creates a new ssh key using the provided email Generating public/private rsa key pair. 
Enter file in which to save the key (/home/you/.ssh/id_rsa):
现在你可以看到，在自己的目录下，有一个.ssh目录，说明成功了
3.1 输入github密码

Enter passphrase (empty for no passphrase): [Type a passphrase] 
Enter same passphrase again: [Type passphrase again]
这个时候输入你在github上设置的密码。
3.2 然后在.ssh中可以看到

Your identification has been saved in /home/you/.ssh/id_rsa. 
`# Your public key has been saved in /home/you/.ssh/id_rsa.pub.
`# The key fingerprint is: 
`# 01:0f:f4:3b:ca:85:d6:17:a1:7d:f0:68:9d:f0:a2:db your_email@youremail.com

4 添加SSH公钥到github
打开github，找到账户里面添加SSH，把idrsa.pub内容复制到key里面。

- 分支
- `git branch -a 查看所有分支
- `git checkout dev 切换到dev分支
- `git merge --no-ff develop 对Develop分支进行合并
- `git push origin dev push到dev分支
分支详解：http://www.ruanyifeng.com/blog/2012/07/git.html