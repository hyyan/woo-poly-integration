#!/usr/bin/env bash

WP_ORG_USERNAME="hyyan"
PLUGIN="woo-poly-integration"
PLUGIN_BUILDS_PATH="./build"
  
cd "$PLUGIN_BUILDS_PATH"
rm -rf *

# prepare files to upload
rsync -av . $PLUGIN_BUILDS_PATH --exclude={build,assets,nbproject,*.json,*.sh,*.png,.*}

# Checkout the SVN repo
svn co -q "http://svn.wp-plugins.org/$PLUGIN" svn

# Move out the trunk directory to a temp location
mv svn/trunk ./svn-trunk

# Create trunk directory
mkdir svn/trunk

# Copy our new version of the plugin into trunk
rsync -r -p $PLUGIN_BUILDS_PATH/* svn/trunk

# Copy all the .svn folders from the checked out copy of trunk to the new trunk.
# This is necessary as the Travis container runs Subversion 1.6 which has .svn dirs in every sub dir
cd svn/trunk/
TARGET=$(pwd)
cd ../../svn-trunk/

# Find all .svn dirs in sub dirs
SVN_DIRS=`find . -type d -iname .svn`

for SVN_DIR in $SVN_DIRS; do
    SOURCE_DIR=${SVN_DIR/.}
    TARGET_DIR=$TARGET${SOURCE_DIR/.svn}
    TARGET_SVN_DIR=$TARGET${SVN_DIR/.}
    if [ -d "$TARGET_DIR" ]; then
        # Copy the .svn directory to trunk dir
        cp -r $SVN_DIR $TARGET_SVN_DIR
    fi
done

# Back to builds dir
cd ../

# Remove checked out dir
rm -fR svn-trunk

# Add new version tag
mkdir svn/tags/$TRAVIS_TAG
rsync -r -p $PLUGIN_BUILDS_PATH/* svn/tags/$TRAVIS_TAG

# Add new files to SVN
svn stat svn | grep '^?' | awk '{print $2}' | xargs -I x svn add x@
# Remove deleted files from SVN
svn stat svn | grep '^!' | awk '{print $2}' | xargs -I x svn rm --force x@
svn stat svn

# Commit to SVN
svn ci --no-auth-cache --username $WP_ORG_USERNAME --password $WP_ORG_PASSWORD svn -m "Deploy version $VERSION"

