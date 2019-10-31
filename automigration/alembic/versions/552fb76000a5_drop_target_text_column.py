"""Drop target text column

Revision ID: 552fb76000a5
Revises: c2116e8b1149
Create Date: 2019-09-18 13:08:08.570891

"""
from alembic import op
import sqlalchemy as sa


# revision identifiers, used by Alembic.
revision = '552fb76000a5'
down_revision = 'c2116e8b1149'
branch_labels = None
depends_on = None


def upgrade():
    # ### commands auto generated by Alembic - please adjust! ###
    op.drop_column('sentences', 'target_text', schema='keopsdb')
    # ### end Alembic commands ###


def downgrade():
    # ### commands auto generated by Alembic - please adjust! ###
    op.add_column('sentences', sa.Column('target_text', sa.VARCHAR(length=5000), autoincrement=False, nullable=True), schema='keopsdb')
    # ### end Alembic commands ###