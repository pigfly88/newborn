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

-----------------------------------------------------------------------------------------------------
git ignore忽略文件
在git中如果想忽略掉某个文件，不让这个文件提交到版本库中，可以使用修改 .gitignore 文件的方法。这个文件每一行保存了一个匹配的规则例如：
# 此为注释 – 将被 Git 忽略
            *.a       # 忽略所有 .a 结尾的文件
            !lib.a    # 但 lib.a 除外
            /TODO     # 仅仅忽略项目根目录下的 TODO 文件，不包括 subdir/TODO
            build/    # 忽略 build/ 目录下的所有文件
            doc/*.txt # 会忽略 doc/notes.txt 但不包括 doc/server/arch.txt
    这样设置了以后 所有的 .pyc 文件都不会添加到版本库中去。
    另外 git 提供了一个全局的 .gitignore，你可以在你的用户目录下创建 ~/.gitignoreglobal 文件，以同样的规则来划定哪些文件是不需要版本控制的。
需要执行 git config --global core.excludesfile ~/.gitignoreglobal来使得它生效。
其他的一些过滤条件
    * ？：代表任意的一个字符
    * ＊：代表任意数目的字符
    * {!ab}：必须不是此类型
    * {ab,bb,cx}：代表ab,bb,cx中任一类型即可
    * [abc]：代表a,b,c中任一字符即可
    * [ ^abc]：代表必须不是a,b,c中任一字符
    由于git不会加入空目录，所以下面做法会导致tmp不会存在 tmp/*             //忽略tmp文件夹所有文件
    改下方法，在tmp下也加一个.gitignore,内容为
                        *
                        !.gitignore
    还有一种情况，就是已经commit了，再加入gitignore是无效的，所以需要删除下缓存
                        git rm -r --cached ignore_file

注意： .gitignore只能忽略那些原来没有被track的文件，如果某些文件已经被纳入了版本管理中，则修改.gitignore是无效的。
    正确的做法是在每个clone下来的仓库中手动设置不要检查特定文件的更改情况。
    git update-index --assume-unchanged PATH    在PATH处输入要忽略的文件。

    另外 git 还提供了另一种 exclude 的方式来做同样的事情，不同的是 .gitignore 这个文件本身会提交到版本库中去。用来保存的是公共的需要排除的文件。而 .git/info/exclude 这里设置的则是你自己本地需要排除的文件。 他不会影响到其他人。也不会提交到版本库中去。

    .gitignore 还有个有意思的小功能， 一个空的 .gitignore 文件 可以当作是一个 placeholder 。当你需要为项目创建一个空的 log 目录时， 这就变的很有用。 你可以创建一个 log 目录 在里面放置一个空的 .gitignore 文件。这样当你 clone 这个 repo 的时候 git 会自动的创建好一个空的 log 目录了。
	
	--------------------------------------------------------------------------------------------
	服务器自动更新git代码
	http://blog.ycnets.com/2013/10/19/automatic-update-version-with-gitlab-web-hook/